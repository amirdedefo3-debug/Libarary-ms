<?php
/**
 * Entry point — redirect to login or appropriate dashboard
 */
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    $role = $_SESSION['user']['role_slug'] ?? 'member';
    $map  = [
        'super_admin' => '/views/admin/dashboard.php',
        'librarian'   => '/views/librarian/dashboard.php',
        'assistant'   => '/views/assistant/dashboard.php',
        'member'      => '/views/member/dashboard.php',
    ];
    redirect(BASE_URL . ($map[$role] ?? '/login.php'));
} else {
    redirect(BASE_URL . '/login.php');
}
