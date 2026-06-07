<?php
/**
 * Reservation Model
 */
class ReservationModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(int $memberId, int $bookId): int|false {
        // Check if already reserved
        $chk = $this->db->prepare("SELECT id FROM reservations WHERE member_id=? AND book_id=? AND status IN('pending','approved')");
        $chk->execute([$memberId, $bookId]);
        if ($chk->fetch()) return false;

        $days = (int)getSetting('reservation_days', '3');
        $resNum = generateUID('RES');
        $stmt = $this->db->prepare(
            "INSERT INTO reservations (reservation_number,member_id,book_id,reserved_date,expiry_date,status)
             VALUES (?,?,?,CURDATE(),DATE_ADD(CURDATE(),INTERVAL ? DAY),'pending')"
        );
        $stmt->execute([$resNum, $memberId, $bookId, $days]);
        return (int)$this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status, ?int $approvedBy = null): bool {
        $stmt = $this->db->prepare("UPDATE reservations SET status=?, approved_by=? WHERE id=?");
        return $stmt->execute([$status, $approvedBy, $id]);
    }

    public function getAll(int $page = 1, int $perPage = 20, string $status = ''): array {
        $offset = ($page - 1) * $perPage;
        $where = ['1=1'];
        $params = [];
        if ($status) {
            $where[] = "r.status = :status";
            $params[':status'] = $status;
        }
        $sql = "SELECT r.*, u.full_name AS member_name, b.title AS book_title, m.member_id AS member_code
                FROM reservations r
                JOIN members m ON r.member_id=m.id
                JOIN users u ON m.user_id=u.id
                JOIN books b ON r.book_id=b.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY r.created_at DESC LIMIT :lim OFFSET :off";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $c = $this->db->prepare("SELECT COUNT(*) FROM reservations r WHERE " . implode(' AND ', $where));
        foreach ($params as $k => $v) $c->bindValue($k, $v);
        $c->execute();
        return ['data' => $rows, 'total' => (int)$c->fetchColumn()];
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.full_name AS member_name, b.title AS book_title
             FROM reservations r JOIN members m ON r.member_id=m.id
             JOIN users u ON m.user_id=u.id JOIN books b ON r.book_id=b.id
             WHERE r.id=? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function countByStatus(string $status): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations WHERE status=?");
        $stmt->execute([$status]);
        return (int)$stmt->fetchColumn();
    }

    public function getMemberReservations(int $memberId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, b.title AS book_title, b.cover_image
             FROM reservations r JOIN books b ON r.book_id=b.id
             WHERE r.member_id=? ORDER BY r.created_at DESC"
        );
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    }
}
