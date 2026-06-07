<?php
/**
 * Borrow/Return Transaction Model
 */
class TransactionModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function issue(int $memberId, int $bookId, int $issuedBy): int|false {
        // Validate availability
        $book = $this->db->prepare("SELECT available_quantity FROM books WHERE id = ?");
        $book->execute([$bookId]);
        $b = $book->fetch();
        if (!$b || $b['available_quantity'] < 1) return false;

        // Borrow limit
        $memberModel = new MemberModel();
        $member = $memberModel->findById($memberId);
        if (!$member) return false;
        $active = $memberModel->getActiveBorrowCount($memberId);
        if ($active >= $member['max_borrow_limit']) return false;

        $borrowDays = (int)getSetting('borrow_days', '14');
        $issueNum = generateUID('ISS');
        $stmt = $this->db->prepare(
            "INSERT INTO borrow_transactions (issue_number,member_id,book_id,issued_by,issue_date,due_date,status)
             VALUES (?,?,?,?,CURDATE(),DATE_ADD(CURDATE(), INTERVAL ? DAY),'borrowed')"
        );
        $stmt->execute([$issueNum, $memberId, $bookId, $issuedBy, $borrowDays]);
        $id = (int)$this->db->lastInsertId();

        // Decrement stock
        $this->db->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?")->execute([$bookId]);
        return $id;
    }

    public function returnBook(int $borrowId, int $returnedBy, string $condition = 'good'): array {
        $stmt = $this->db->prepare("SELECT * FROM borrow_transactions WHERE id = ? LIMIT 1");
        $stmt->execute([$borrowId]);
        $borrow = $stmt->fetch();
        if (!$borrow) return ['success' => false, 'message' => 'Transaction not found'];

        $fine = calcFine($borrow['due_date']);

        // Update borrow transaction
        $this->db->prepare("UPDATE borrow_transactions SET status='returned', return_date=CURDATE() WHERE id=?")->execute([$borrowId]);

        // Insert return record
        $this->db->prepare(
            "INSERT INTO return_transactions (borrow_id,returned_to,return_date,book_condition,fine_amount)
             VALUES (?,?,CURDATE(),?,?)"
        )->execute([$borrowId, $returnedBy, $condition, $fine['amount']]);

        // Increment book stock
        $this->db->prepare("UPDATE books SET available_quantity = available_quantity + 1 WHERE id=?")->execute([$borrow['book_id']]);

        // Create fine record if applicable
        if ($fine['amount'] > 0) {
            $this->db->prepare(
                "INSERT INTO fines (borrow_id,member_id,amount,days_overdue,status) VALUES (?,?,?,?,'pending')"
            )->execute([$borrowId, $borrow['member_id'], $fine['amount'], $fine['days']]);
        }

        return ['success' => true, 'fine' => $fine['amount'], 'days_overdue' => $fine['days']];
    }

    public function getAll(int $page = 1, int $perPage = 20, array $filters = []): array {
        $offset = ($page - 1) * $perPage;
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = "bt.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['member_id'])) {
            $where[] = "bt.member_id = :mid";
            $params[':mid'] = $filters['member_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(u.full_name LIKE :s OR b.title LIKE :s OR bt.issue_number LIKE :s)";
            $params[':s'] = '%' . $filters['search'] . '%';
        }
        $sql = "SELECT bt.*, u.full_name AS member_name, b.title AS book_title, b.isbn,
                       lu.full_name AS issued_by_name, m.member_id AS member_code
                FROM borrow_transactions bt
                JOIN members m ON bt.member_id = m.id
                JOIN users u ON m.user_id = u.id
                JOIN books b ON bt.book_id = b.id
                JOIN users lu ON bt.issued_by = lu.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY bt.created_at DESC LIMIT :lim OFFSET :off";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $c = $this->db->prepare("SELECT COUNT(*) FROM borrow_transactions bt
            JOIN members m ON bt.member_id=m.id JOIN users u ON m.user_id=u.id
            JOIN books b ON bt.book_id=b.id WHERE " . implode(' AND ', $where));
        foreach ($params as $k => $v) $c->bindValue($k, $v);
        $c->execute();
        return ['data' => $rows, 'total' => (int)$c->fetchColumn()];
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT bt.*, u.full_name AS member_name, b.title AS book_title, m.member_id AS member_code
             FROM borrow_transactions bt
             JOIN members m ON bt.member_id = m.id
             JOIN users u ON m.user_id = u.id
             JOIN books b ON bt.book_id = b.id
             WHERE bt.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function countByStatus(string $status): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE status = ?");
        $stmt->execute([$status]);
        return (int)$stmt->fetchColumn();
    }

    public function countTodayIssued(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM borrow_transactions WHERE DATE(issue_date)=CURDATE()")->fetchColumn();
    }

    public function countTodayReturned(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM borrow_transactions WHERE DATE(return_date)=CURDATE()")->fetchColumn();
    }

    public function getOverdue(): array {
        $stmt = $this->db->query(
            "SELECT bt.*, u.full_name, b.title AS book_title, m.member_id AS member_code
             FROM borrow_transactions bt
             JOIN members m ON bt.member_id=m.id
             JOIN users u ON m.user_id=u.id
             JOIN books b ON bt.book_id=b.id
             WHERE bt.status='borrowed' AND bt.due_date < CURDATE()
             ORDER BY bt.due_date ASC"
        );
        return $stmt->fetchAll();
    }
}
