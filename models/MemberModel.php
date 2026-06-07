<?php
/**
 * Member Model
 */
class MemberModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(int $page = 1, int $perPage = 20, string $search = ''): array {
        $offset = ($page - 1) * $perPage;
        $where = ['1=1'];
        $params = [];
        if ($search) {
            $where[] = "(u.full_name LIKE :s OR m.member_id LIKE :s OR u.email LIKE :s)";
            $params[':s'] = "%$search%";
        }
        $sql = "SELECT m.*, u.full_name, u.email, u.phone, u.photo, u.gender, u.status AS user_status
                FROM members m JOIN users u ON m.user_id = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY m.created_at DESC LIMIT :lim OFFSET :off";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $c = $this->db->prepare("SELECT COUNT(*) FROM members m JOIN users u ON m.user_id=u.id WHERE " . implode(' AND ', $where));
        foreach ($params as $k => $v) $c->bindValue($k, $v);
        $c->execute();
        return ['data' => $rows, 'total' => (int)$c->fetchColumn()];
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT m.*, u.full_name, u.email, u.phone, u.photo, u.gender, u.address, u.department,
                    u.status AS user_status
             FROM members m JOIN users u ON m.user_id = u.id WHERE m.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUserId(int $userId): ?array {
        $stmt = $this->db->prepare(
            "SELECT m.*, u.full_name, u.email, u.phone, u.photo
             FROM members m JOIN users u ON m.user_id = u.id WHERE m.user_id = ? LIMIT 1"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $userId, array $data): int {
        $memberId = generateUID('MEM');
        $stmt = $this->db->prepare(
            "INSERT INTO members (user_id,member_id,student_id,department,membership_date,expiry_date,max_borrow_limit,status)
             VALUES (?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $userId,
            $memberId,
            $data['student_id'] ?? null,
            $data['department'] ?? null,
            $data['membership_date'] ?? date('Y-m-d'),
            $data['expiry_date'] ?? date('Y-m-d', strtotime('+1 year')),
            $data['max_borrow_limit'] ?? 5,
            'active'
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];
        foreach (['student_id','department','membership_date','expiry_date','max_borrow_limit','status'] as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "`$f` = :$f";
                $params[":$f"] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        return $this->db->prepare("UPDATE members SET " . implode(',', $fields) . " WHERE id = :id")->execute($params);
    }

    public function getActiveBorrowCount(int $memberId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE member_id = ? AND status = 'borrowed'");
        $stmt->execute([$memberId]);
        return (int)$stmt->fetchColumn();
    }

    public function count(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM members")->fetchColumn();
    }

    public function countActive(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM members WHERE status='active'")->fetchColumn();
    }

    public function countNewToday(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM members WHERE DATE(created_at)=CURDATE()")->fetchColumn();
    }
}
