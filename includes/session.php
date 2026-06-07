<?php
/**
 * Session Configuration & Security
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    // ini_set('session.cookie_secure', 1); // Enable on HTTPS
    session_start();
}

// Session timeout (minutes from settings, default 30)
$timeout = 30;
if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > ($timeout * 60)) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/login.php?timeout=1');
        exit;
    }
}
$_SESSION['last_activity'] = time();

// CSRF token generation
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}
