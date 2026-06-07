<?php
/**
 * Global Helper Functions
 */

// ── Settings cache — loaded ONCE per request ──────────────
$_SETTINGS_CACHE = null;

function loadAllSettings(): void {
    global $_SETTINGS_CACHE;
    if ($_SETTINGS_CACHE !== null) return;
    try {
        $db   = Database::getInstance();
        $rows = $db->query("SELECT `key`, value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        $_SETTINGS_CACHE = $rows;
    } catch (Exception $e) {
        $_SETTINGS_CACHE = [];
    }
}

function getSetting(string $key, string $default = ''): string {
    global $_SETTINGS_CACHE;
    loadAllSettings();
    return $_SETTINGS_CACHE[$key] ?? $default;
}

// ── XSS protection ────────────────────────────────────────
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ── Redirect ──────────────────────────────────────────────
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// ── Flash messages ────────────────────────────────────────
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ── Auth helpers ──────────────────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function hasRole(string $role): bool {
    return isset($_SESSION['user']['role_slug']) && $_SESSION['user']['role_slug'] === $role;
}

function hasPermission(string $slug): bool {
    return isset($_SESSION['permissions']) && in_array($slug, $_SESSION['permissions'], true);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/login.php');
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array($_SESSION['user']['role_slug'] ?? '', $roles, true)) {
        redirect(BASE_URL . '/unauthorized.php');
    }
}

function requirePermission(string $perm): void {
    requireLogin();
    if (!hasPermission($perm)) {
        redirect(BASE_URL . '/unauthorized.php');
    }
}

// ── UID generator ─────────────────────────────────────────
function generateUID(string $prefix = ''): string {
    return strtoupper($prefix) . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

// ── File upload ───────────────────────────────────────────
function uploadFile(array $file, string $dest, array $allowedTypes, int $maxSize = 5242880): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > $maxSize) return false;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedTypes, true)) return false;
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
    if (!is_dir($dest)) mkdir($dest, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $dest . $filename)) return false;
    return $filename;
}

// ── Date / currency ───────────────────────────────────────
function formatDate(?string $date, string $format = 'd M Y'): string {
    if (!$date) return '—';
    return date($format, strtotime($date));
}

function currency(float $amount): string {
    return getSetting('currency_symbol', '$') . number_format($amount, 2);
}

// ── Fine calculator (uses cached settings) ────────────────
function calcFine(string $dueDate): array {
    $fpd     = (float)getSetting('fine_per_day', '5');
    $maxFine = (float)getSetting('max_fine', '500');
    $today   = new DateTime();
    $due     = new DateTime($dueDate);
    $days    = ($today > $due) ? (int)$today->diff($due)->days : 0;
    $amount  = min($days * $fpd, $maxFine);
    return ['days' => $days, 'amount' => $amount];
}

// ── Pagination ────────────────────────────────────────────
function paginate(int $total, int $perPage, int $currentPage): array {
    $totalPages = (int)ceil($total / max(1, $perPage));
    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => ($currentPage - 1) * $perPage,
    ];
}

// ── Activity log (non-blocking: errors are swallowed) ─────
function logActivity(string $action, string $module = '', string $description = ''): void {
    try {
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO activity_logs (user_id, action, module, description, ip_address)
             VALUES (?,?,?,?,?)"
        );
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $action,
            $module,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
    } catch (Exception $e) {
        // Never crash a page because of logging
    }
}

// ── Notifications ─────────────────────────────────────────
function sendNotification(int $userId, string $title, string $message, string $type = 'general'): void {
    try {
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)"
        );
        $stmt->execute([$userId, $title, $message, $type]);
    } catch (Exception $e) {
        // Swallow silently
    }
}
