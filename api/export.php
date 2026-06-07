<?php
/**
 * CSV Export API
 */
require_once __DIR__ . '/../config/config.php';
requirePermission('reports.view');

$type   = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'csv';
$db     = Database::getInstance();

if ($format === 'csv') {
    if ($type === 'books') {
        $rows = $db->query(
            "SELECT b.id, b.isbn, b.title, b.subtitle, a.name AS author, p.name AS publisher,
                    c.name AS category, b.edition, b.language, b.shelf_number, b.rack_number,
                    b.quantity, b.available_quantity, b.price, b.purchase_date, b.status
             FROM books b
             LEFT JOIN authors a ON b.author_id=a.id
             LEFT JOIN publishers p ON b.publisher_id=p.id
             LEFT JOIN categories c ON b.category_id=c.id
             ORDER BY b.title"
        )->fetchAll();
        $headers = ['ID','ISBN','Title','Subtitle','Author','Publisher','Category','Edition','Language','Shelf','Rack','Qty','Available','Price','Purchase Date','Status'];
        $filename = 'books_' . date('Ymd') . '.csv';

    } elseif ($type === 'members') {
        $rows = $db->query(
            "SELECT m.member_id, u.full_name, u.email, u.phone, u.gender, u.department,
                    m.membership_date, m.expiry_date, m.status
             FROM members m JOIN users u ON m.user_id=u.id ORDER BY u.full_name"
        )->fetchAll();
        $headers = ['Member ID','Full Name','Email','Phone','Gender','Department','Membership Date','Expiry Date','Status'];
        $filename = 'members_' . date('Ymd') . '.csv';

    } elseif ($type === 'transactions') {
        $rows = $db->query(
            "SELECT bt.issue_number, u.full_name AS member, m.member_id AS member_code,
                    b.title AS book, bt.issue_date, bt.due_date, bt.return_date, bt.status
             FROM borrow_transactions bt
             JOIN members m ON bt.member_id=m.id
             JOIN users u ON m.user_id=u.id
             JOIN books b ON bt.book_id=b.id
             ORDER BY bt.created_at DESC"
        )->fetchAll();
        $headers = ['Issue #','Member','Member ID','Book','Issue Date','Due Date','Return Date','Status'];
        $filename = 'transactions_' . date('Ymd') . '.csv';

    } else {
        die('Invalid export type');
    }

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, array_values($row));
    }
    fclose($out);
    exit;
}

die('Unsupported format');
