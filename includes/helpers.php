<?php
/**
 * Global Helper Functions
 */

/**
 * Sanitize output to prevent XSS
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Redirect helper
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Flash message setter
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Flash message getter & clear
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user from session
 */
function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Check user role
 */
function hasRole(string $role): bool {
    return isset($_SESSION['user']['role_slug']) && $_SESSION['user']['role_slug'] === $role;
}

/**
 * Check if user has a permission slug
 */
function hasPermission(string $slug): bool {
    return isset($_SESSION['permissions']) && in_array($slug, $_SESSION['permissions']);
}

/**
 * Require login — redirect if not logged in
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/login.php');
    }
}

/**
 * Require a specific role
 */
function requireRole(string ...$roles): void {
    requireLogin();
    $userRole = $_SESSION['user']['role_slug'] ?? '';
    if (!in_array($userRole, $roles)) {
        redirect(BASE_URL . '/unauthorized.php');
    }
}

/**
 * Require a specific permission
 */
function requirePermission(string $perm): void {
    requireLogin();
    if (!hasPermission($perm)) {
        redirect(BASE_URL . '/unauthorized.php');
    }
}

/**
 * Generate unique ID with prefix
 */
function generateUID(string $prefix = ''): string {
    return strtoupper($prefix) . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

/**
 * Upload a file safely
 */
function uploadFile(array $file, string $dest, array $allowedTypes, int $maxSize = 5242880): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > $maxSize) return false;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedTypes)) return false;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
    $fullPath = $dest . $filename;
    if (!is_dir($dest)) mkdir($dest, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $fullPath)) return false;
    return $filename;
}

/**
 * Format date
 */
function formatDate(?string $date, string $format = 'd M Y'): string {
    if (!$date) return '—';
    return date($format, strtotime($date));
}

/**
 * Format currency
 */
function currency(float $amount): string {
    $symbol = '$';
    return $symbol . number_format($amount, 2);
}

/**
 * Calculate overdue fine
 */
function calcFine(string $dueDate): array {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT value FROM settings WHERE `key` = 'fine_per_day'");
    $fpd = (float)($stmt->fetchColumn() ?: 5);
    $stmt = $db->query("SELECT value FROM settings WHERE `key` = 'max_fine'");
    $maxFine = (float)($stmt->fetchColumn() ?: 500);

    $today = new DateTime();
    $due   = new DateTime($dueDate);
    $diff  = $today->diff($due);
    $days  = ($today > $due) ? (int)$diff->days : 0;
    $amount = min($days * $fpd, $maxFine);
    return ['days' => $days, 'amount' => $amount];
}

/**
 * Paginate query results
 */
function paginate(int $total, int $perPage, int $currentPage): array {
    $totalPages = (int)ceil($total / $perPage);
    $offset     = ($currentPage - 1) * $perPage;
    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => $offset,
    ];
}

/**
 * Log an activity
 */
function logActivity(string $action, string $module = '', string $description = ''): void {
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, module, description, ip_address) VALUES (?,?,?,?,?)");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $action,
            $module,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log('logActivity error: ' . $e->getMessage());
    }
}

/**
 * Get setting value
 */
function getSetting(string $key, string $default = ''): string {
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Send a notification to a user
 */
function sendNotification(int $userId, string $title, string $message, string $type = 'general'): void {
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)");
        $stmt->execute([$userId, $title, $message, $type]);
    } catch (Exception $e) {
        error_log('sendNotification error: ' . $e->getMessage());
    }
}
