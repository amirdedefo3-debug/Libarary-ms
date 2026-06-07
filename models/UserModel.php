<?php
/**
 * User Model
 */
class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u JOIN roles r ON u.role_id = r.id
             WHERE u.email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u JOIN roles r ON u.role_id = r.id
             WHERE u.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u JOIN roles r ON u.role_id = r.id
             WHERE u.username = ? LIMIT 1"
        );
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function getPermissions(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT p.slug FROM permissions p
             JOIN role_permissions rp ON p.id = rp.permission_id
             JOIN users u ON u.role_id = rp.role_id
             WHERE u.id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO users (role_id,username,email,password,full_name,phone,gender,department,address,status)
             VALUES (:role_id,:username,:email,:password,:full_name,:phone,:gender,:department,:address,:status)"
        );
        $stmt->execute([
            ':role_id'   => $data['role_id'],
            ':username'  => $data['username'],
            ':email'     => $data['email'],
            ':password'  => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':full_name' => $data['full_name'],
            ':phone'     => $data['phone'] ?? null,
            ':gender'    => $data['gender'] ?? null,
            ':department'=> $data['department'] ?? null,
            ':address'   => $data['address'] ?? null,
            ':status'    => $data['status'] ?? 'active',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];
        $allowed = ['full_name','phone','email','gender','department','address','status','role_id','photo'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "`$f` = :$f";
                $params[":$f"] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $params[':id'] = $id;
        $stmt = $this->db->prepare("UPDATE users SET " . implode(',', $fields) . " WHERE id = :id");
        return $stmt->execute($params);
    }

    public function updatePassword(int $id, string $password): bool {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $id]);
    }

    public function incrementFailedAttempts(int $id): void {
        $this->db->prepare("UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = ?")->execute([$id]);
        // Lock account after max attempts
        $max = (int)getSetting('max_failed_attempts', '5');
        $stmt = $this->db->prepare("SELECT failed_attempts FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $attempts = (int)$stmt->fetchColumn();
        if ($attempts >= $max) {
            $lockUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $this->db->prepare("UPDATE users SET locked_until = ? WHERE id = ?")->execute([$lockUntil, $id]);
        }
    }

    public function resetFailedAttempts(int $id): void {
        $this->db->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?")->execute([$id]);
    }

    public function setResetToken(int $id, string $token): void {
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $this->db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?")->execute([$token, $expires, $id]);
    }

    public function findByResetToken(string $token): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1"
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function clearResetToken(int $id): void {
        $this->db->prepare("UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE id = ?")->execute([$id]);
    }

    public function updateLastLogin(int $id): void {
        $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$id]);
    }

    public function logLogin(int $userId, string $status = 'success'): void {
        $stmt = $this->db->prepare(
            "INSERT INTO login_history (user_id, ip_address, user_agent, status) VALUES (?,?,?,?)"
        );
        $stmt->execute([
            $userId,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $status
        ]);
    }

    public function getAll(int $page = 1, int $perPage = 20, string $search = '', string $role = ''): array {
        $offset = ($page - 1) * $perPage;
        $where = ['1=1'];
        $params = [];
        if ($search) {
            $where[] = "(u.full_name LIKE :search OR u.email LIKE :search OR u.username LIKE :search)";
            $params[':search'] = "%$search%";
        }
        if ($role) {
            $where[] = "r.slug = :role";
            $params[':role'] = $role;
        }
        $sql = "SELECT u.*, r.name AS role_name, r.slug AS role_slug
                FROM users u JOIN roles r ON u.role_id = r.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        // Count
        $countSql = "SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE " . implode(' AND ', $where);
        $countStmt = $this->db->prepare($countSql);
        foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        return ['data' => $rows, 'total' => $total];
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }

    public function countByRole(string $slug): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.slug = ?");
        $stmt->execute([$slug]);
        return (int)$stmt->fetchColumn();
    }
}
