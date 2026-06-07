<?php
/**
 * Book Model
 */
class BookModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(int $page = 1, int $perPage = 20, array $filters = []): array {
        $offset = ($page - 1) * $perPage;
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = "(b.title LIKE :search OR b.isbn LIKE :search OR a.name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category_id'])) {
            $where[] = "b.category_id = :cat";
            $params[':cat'] = $filters['category_id'];
        }
        if (!empty($filters['language'])) {
            $where[] = "b.language = :lang";
            $params[':lang'] = $filters['language'];
        }
        if (isset($filters['available']) && $filters['available'] === '1') {
            $where[] = "b.available_quantity > 0";
        }

        $sql = "SELECT b.*, c.name AS category_name, a.name AS author_name, p.name AS publisher_name
                FROM books b
                LEFT JOIN categories c ON b.category_id = c.id
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN publishers p ON b.publisher_id = p.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $countSql = "SELECT COUNT(*) FROM books b LEFT JOIN authors a ON b.author_id = a.id WHERE " . implode(' AND ', $where);
        $countStmt = $this->db->prepare($countSql);
        foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        return ['data' => $rows, 'total' => $total];
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT b.*, c.name AS category_name, a.name AS author_name, p.name AS publisher_name
             FROM books b
             LEFT JOIN categories c ON b.category_id = c.id
             LEFT JOIN authors a ON b.author_id = a.id
             LEFT JOIN publishers p ON b.publisher_id = p.id
             WHERE b.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO books (isbn,barcode,title,subtitle,author_id,publisher_id,category_id,edition,
             language,shelf_number,rack_number,quantity,available_quantity,price,purchase_date,
             description,cover_image,pdf_file)
             VALUES (:isbn,:barcode,:title,:subtitle,:author_id,:publisher_id,:category_id,:edition,
             :language,:shelf_number,:rack_number,:quantity,:available_quantity,:price,:purchase_date,
             :description,:cover_image,:pdf_file)"
        );
        $stmt->execute([
            ':isbn'               => $data['isbn'] ?? null,
            ':barcode'            => $data['barcode'] ?? null,
            ':title'              => $data['title'],
            ':subtitle'           => $data['subtitle'] ?? null,
            ':author_id'          => $data['author_id'] ?? null,
            ':publisher_id'       => $data['publisher_id'] ?? null,
            ':category_id'        => $data['category_id'] ?? null,
            ':edition'            => $data['edition'] ?? null,
            ':language'           => $data['language'] ?? 'English',
            ':shelf_number'       => $data['shelf_number'] ?? null,
            ':rack_number'        => $data['rack_number'] ?? null,
            ':quantity'           => $data['quantity'] ?? 1,
            ':available_quantity' => $data['quantity'] ?? 1,
            ':price'              => $data['price'] ?? 0,
            ':purchase_date'      => $data['purchase_date'] ?? null,
            ':description'        => $data['description'] ?? null,
            ':cover_image'        => $data['cover_image'] ?? 'default_book.png',
            ':pdf_file'           => $data['pdf_file'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        $allowed = ['isbn','barcode','title','subtitle','author_id','publisher_id','category_id','edition',
                    'language','shelf_number','rack_number','quantity','available_quantity','price',
                    'purchase_date','description','cover_image','pdf_file','status'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "`$f` = :$f";
                $params[":$f"] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        return $this->db->prepare("UPDATE books SET " . implode(',', $fields) . " WHERE id = :id")->execute($params);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM books WHERE id = ?")->execute([$id]);
    }

    public function decrementStock(int $id): void {
        $this->db->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ? AND available_quantity > 0")->execute([$id]);
    }

    public function incrementStock(int $id): void {
        $this->db->prepare("UPDATE books SET available_quantity = available_quantity + 1 WHERE id = ?")->execute([$id]);
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM books")->fetchColumn();
    }

    public function countAvailable(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM books WHERE available_quantity > 0")->fetchColumn();
    }

    public function getMostBorrowed(int $limit = 10): array {
        $stmt = $this->db->prepare(
            "SELECT b.title, COUNT(bt.id) AS borrow_count
             FROM borrow_transactions bt
             JOIN books b ON bt.book_id = b.id
             GROUP BY bt.book_id ORDER BY borrow_count DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getMonthlyStats(int $months = 12): array {
        $stmt = $this->db->query(
            "SELECT DATE_FORMAT(issue_date,'%Y-%m') AS month, COUNT(*) AS borrows
             FROM borrow_transactions
             GROUP BY month ORDER BY month DESC LIMIT $months"
        );
        return $stmt->fetchAll();
    }
}
