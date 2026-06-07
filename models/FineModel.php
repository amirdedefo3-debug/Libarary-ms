<?php
/**
 * Fine & Payment Model
 */
class FineModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(int $page = 1, int $perPage = 20, string $status = ''): array {
        $offset = ($page - 1) * $perPage;
        $where = ['1=1'];
        $params = [];
        if ($status) {
            $where[] = "f.status = :status";
            $params[':status'] = $status;
        }
        $sql = "SELECT f.*, u.full_name AS member_name, b.title AS book_title,
                       bt.issue_date, bt.due_date, m.member_id AS member_code
                FROM fines f
                JOIN borrow_transactions bt ON f.borrow_id=bt.id
                JOIN members m ON f.member_id=m.id
                JOIN users u ON m.user_id=u.id
                JOIN books b ON bt.book_id=b.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY f.created_at DESC LIMIT :lim OFFSET :off";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $c = $this->db->prepare("SELECT COUNT(*) FROM fines f WHERE " . implode(' AND ', $where));
        foreach ($params as $k => $v) $c->bindValue($k, $v);
        $c->execute();
        return ['data' => $rows, 'total' => (int)$c->fetchColumn()];
    }

    public function payFine(int $fineId, float $amount, int $receivedBy, string $method = 'cash'): int {
        $this->db->prepare("UPDATE fines SET status='paid' WHERE id=?")->execute([$fineId]);
        $receiptNum = generateUID('RCP');
        $stmt = $this->db->prepare(
            "INSERT INTO payments (fine_id,amount_paid,payment_method,received_by,receipt_number) VALUES (?,?,?,?,?)"
        );
        $stmt->execute([$fineId, $amount, $method, $receivedBy, $receiptNum]);
        return (int)$this->db->lastInsertId();
    }

    public function waiveFine(int $fineId): bool {
        return $this->db->prepare("UPDATE fines SET status='waived' WHERE id=?")->execute([$fineId]);
    }

    public function totalCollected(): float {
        return (float)$this->db->query("SELECT COALESCE(SUM(amount_paid),0) FROM payments")->fetchColumn();
    }

    public function totalPending(): float {
        return (float)$this->db->query("SELECT COALESCE(SUM(amount),0) FROM fines WHERE status='pending'")->fetchColumn();
    }

    public function getMemberFines(int $memberId): array {
        $stmt = $this->db->prepare(
            "SELECT f.*, b.title AS book_title, bt.due_date
             FROM fines f JOIN borrow_transactions bt ON f.borrow_id=bt.id
             JOIN books b ON bt.book_id=b.id
             WHERE f.member_id=? ORDER BY f.created_at DESC"
        );
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    }

    public function getMonthlyCollected(int $months = 12): array {
        $stmt = $this->db->query(
            "SELECT DATE_FORMAT(payment_date,'%Y-%m') AS month, SUM(amount_paid) AS total
             FROM payments GROUP BY month ORDER BY month DESC LIMIT $months"
        );
        return $stmt->fetchAll();
    }
}
