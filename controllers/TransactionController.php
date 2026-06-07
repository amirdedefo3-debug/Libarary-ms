<?php
/**
 * Issue / Return Controller
 */
require_once BASE_PATH . '/models/TransactionModel.php';
require_once BASE_PATH . '/models/MemberModel.php';
require_once BASE_PATH . '/models/BookModel.php';

class TransactionController {
    private TransactionModel $model;

    public function __construct() {
        $this->model = new TransactionModel();
    }

    /** List all borrow transactions */
    public function index(): void {
        requirePermission('transactions.issue');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => trim($_GET['search'] ?? ''),
        ];
        $result      = $this->model->getAll($page, 20, $filters);
        $transactions = $result['data'];
        $pagination   = paginate($result['total'], 20, $page);
        include BASE_PATH . '/views/admin/transactions/index.php';
    }

    /** Issue a book form + handle */
    public function issue(): void {
        requirePermission('transactions.issue');
        $error   = '';
        $success = '';
        $memberModel = new MemberModel();
        $bookModel   = new BookModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid token.';
            } else {
                $memberId = (int)($_POST['member_id'] ?? 0);
                $bookId   = (int)($_POST['book_id'] ?? 0);

                $member = $memberModel->findById($memberId);
                $book   = $bookModel->findById($bookId);

                if (!$member) { $error = 'Member not found.'; }
                elseif ($member['status'] !== 'active') { $error = 'Member is not active.'; }
                elseif (!$book) { $error = 'Book not found.'; }
                elseif ($book['available_quantity'] < 1) { $error = 'Book not available.'; }
                else {
                    $id = $this->model->issue($memberId, $bookId, $_SESSION['user_id']);
                    if ($id === false) {
                        $error = 'Could not issue: borrow limit reached or book unavailable.';
                    } else {
                        // Notify member
                        sendNotification(
                            $member['user_id'],
                            'Book Issued',
                            '"' . $book['title'] . '" has been issued to you. Due date: ' . date('d M Y', strtotime('+' . getSetting('borrow_days','14') . ' days')),
                            'general'
                        );
                        logActivity('issue_book', 'transactions', "Issued '{$book['title']}' to member #{$memberId}");
                        setFlash('success', 'Book issued successfully!');
                        redirect(BASE_URL . '/views/admin/transactions/index.php');
                    }
                }
            }
        }
        include BASE_PATH . '/views/admin/transactions/issue.php';
    }

    /** Return a book form + handle */
    public function returnBook(): void {
        requirePermission('transactions.return');
        $error   = '';
        $borrow  = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid token.';
            } else {
                $borrowId  = (int)($_POST['borrow_id'] ?? 0);
                $condition = $_POST['condition'] ?? 'good';
                $result    = $this->model->returnBook($borrowId, $_SESSION['user_id'], $condition);
                if ($result['success']) {
                    $msg = 'Book returned successfully.';
                    if ($result['fine'] > 0) {
                        $msg .= ' Fine applied: ' . currency($result['fine']) . ' (' . $result['days_overdue'] . ' days overdue)';
                    }
                    logActivity('return_book', 'transactions', "Returned borrow #$borrowId");
                    setFlash('success', $msg);
                    redirect(BASE_URL . '/views/admin/transactions/index.php');
                } else {
                    $error = $result['message'] ?? 'Return failed.';
                }
            }
        }

        // AJAX/Search for borrow by issue number
        if (!empty($_GET['issue_number'])) {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                "SELECT bt.*, b.title AS book_title, u.full_name AS member_name, bt.issue_number
                 FROM borrow_transactions bt
                 JOIN books b ON bt.book_id=b.id
                 JOIN members m ON bt.member_id=m.id
                 JOIN users u ON m.user_id=u.id
                 WHERE bt.issue_number = ? AND bt.status='borrowed' LIMIT 1"
            );
            $stmt->execute([trim($_GET['issue_number'])]);
            $borrow = $stmt->fetch() ?: null;
        }

        include BASE_PATH . '/views/admin/transactions/return.php';
    }

    public function overdue(): void {
        requirePermission('transactions.issue');
        $transactions = $this->model->getOverdue();
        include BASE_PATH . '/views/admin/transactions/overdue.php';
    }
}
