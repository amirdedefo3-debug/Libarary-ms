<?php
/**
 * Smart sidebar router — automatically includes the correct sidebar
 * based on the logged-in user's role.
 * Usage: replace all `include sidebar_admin.php` / `sidebar_librarian.php` etc.
 * with: include __DIR__ . '/../includes/sidebar.php';
 */
$_role = $_SESSION['user']['role_slug'] ?? 'member';
$_sidebarMap = [
    'super_admin' => 'sidebar_admin.php',
    'librarian'   => 'sidebar_librarian.php',
    'assistant'   => 'sidebar_assistant.php',
    'member'      => 'sidebar_member.php',
];
$_sidebarFile = __DIR__ . '/' . ($_sidebarMap[$_role] ?? 'sidebar_member.php');
if (file_exists($_sidebarFile)) {
    include $_sidebarFile;
}
