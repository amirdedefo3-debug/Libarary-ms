<?php
/**
 * Role-Based Access Control Middleware
 * Include this at the top of any protected page.
 *
 * Usage examples:
 *   middleware('super_admin');
 *   middleware(['super_admin','librarian']);
 *   middlewarePermission('books.delete');
 */

function middleware(string|array $roles): void {
    requireLogin();
    $allowed = is_array($roles) ? $roles : [$roles];
    $userRole = $_SESSION['user']['role_slug'] ?? '';
    if (!in_array($userRole, $allowed)) {
        setFlash('error', 'You do not have permission to access that page.');
        redirect(BASE_URL . '/unauthorized.php');
    }
}

function middlewarePermission(string $permission): void {
    requireLogin();
    if (!hasPermission($permission)) {
        setFlash('error', 'Permission denied: ' . $permission);
        redirect(BASE_URL . '/unauthorized.php');
    }
}
