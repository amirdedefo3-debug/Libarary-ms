<?php
/**
 * Notifications API
 */
require_once __DIR__ . '/../config/config.php';
requireLogin();

header('Content-Type: application/json');
$db     = Database::getInstance();
$action = $_GET['action'] ?? '';

if ($action === 'unread_count') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode(['count' => (int)$stmt->fetchColumn()]);

} elseif ($action === 'mark_all_read') {
    $db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$_SESSION['user_id']]);
    header('Location: ' . BASE_URL . '/views/admin/dashboard.php');
    exit;

} elseif ($action === 'list') {
    $stmt = $db->prepare(
        "SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20"
    );
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode($stmt->fetchAll());
} else {
    echo json_encode(['error' => 'Invalid action']);
}
