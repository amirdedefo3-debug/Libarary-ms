<?php
/**
 * AJAX Search API — members and books
 */
require_once __DIR__ . '/../config/config.php';

requireLogin();

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$q    = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$db = Database::getInstance();

if ($type === 'member') {
    $stmt = $db->prepare(
        "SELECT m.id, u.full_name, m.member_id, m.status
         FROM members m JOIN users u ON m.user_id=u.id
         WHERE (u.full_name LIKE ? OR m.member_id LIKE ? OR u.email LIKE ?)
         AND m.status='active'
         ORDER BY u.full_name LIMIT 10"
    );
    $stmt->execute(["%$q%", "%$q%", "%$q%"]);
    echo json_encode($stmt->fetchAll());

} elseif ($type === 'book') {
    $stmt = $db->prepare(
        "SELECT b.id, b.title, b.isbn, b.available_quantity, a.name AS author_name
         FROM books b LEFT JOIN authors a ON b.author_id=a.id
         WHERE (b.title LIKE ? OR b.isbn LIKE ?) AND b.available_quantity > 0
         ORDER BY b.title LIMIT 10"
    );
    $stmt->execute(["%$q%", "%$q%"]);
    echo json_encode($stmt->fetchAll());

} else {
    echo json_encode([]);
}
