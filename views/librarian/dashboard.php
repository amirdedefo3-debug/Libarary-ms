<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['librarian']);

require_once __DIR__ . '/../../models/BookModel.php';
require_once __DIR__ . '/../../models/MemberModel.php';
require_once __DIR__ . '/../../models/TransactionModel.php';
require_once __DIR__ . '/../../models/FineModel.php';

$bookModel   = new BookModel();
$memberModel = new MemberModel();
$txModel     = new TransactionModel();
$fineModel   = new FineModel();
$db          = Database::getInstance();

$totalBooks     = $bookModel->count();
$availableBooks = $bookModel->countAvailable();
$borrowedToday  = $txModel->countTodayIssued();
$returnedToday  = $txModel->countTodayReturned();
$overdue        = (int)$db->query("SELECT COUNT(*) FROM borrow_transactions WHERE status='borrowed' AND due_date < CURDATE()")->fetchColumn();
$activeMembers  = $memberModel->countActive();

$recentTx = $db->query(
    "SELECT bt.issue_date, bt.due_date, b.title AS book_title, u.full_name, bt.status
     FROM borrow_transactions bt
     JOIN books b ON bt.book_id=b.id
     JOIN members m ON bt.member_id=m.id
     JOIN users u ON m.user_id=u.id
     ORDER BY bt.created_at DESC LIMIT 10"
)->fetchAll();

$pageTitle = 'Librarian Dashboard';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <!-- Use admin sidebar with librarian-only links -->
  <?php include __DIR__ . '/../../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Librarian Dashboard</h1>
          <p class="page-breadcrumb">Welcome, <?= e(currentUser()['full_name']) ?>!</p>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Issue Book</a>
          <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Return Book</a>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-book"></i></div>
          <div class="stat-info">
            <div class="stat-label">Available Books</div>
            <div class="stat-value"><?= number_format($availableBooks) ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-exchange-alt"></i></div>
          <div class="stat-info">
            <div class="stat-label">Issued Today</div>
            <div class="stat-value"><?= number_format($borrowedToday) ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-undo"></i></div>
          <div class="stat-info">
            <div class="stat-label">Returned Today</div>
            <div class="stat-value"><?= number_format($returnedToday) ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
          <div class="stat-info">
            <div class="stat-label">Overdue</div>
            <div class="stat-value"><?= number_format($overdue) ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon cyan"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <div class="stat-label">Active Members</div>
            <div class="stat-value"><?= number_format($activeMembers) ?></div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card mb-4">
        <div class="card-header">Quick Actions</div>
        <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;">
          <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Issue Book</a>
          <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="btn btn-success"><i class="fas fa-arrow-left"></i> Return Book</a>
          <a href="<?= BASE_URL ?>/views/admin/books/create.php" class="btn btn-secondary"><i class="fas fa-plus"></i> Add Book</a>
          <a href="<?= BASE_URL ?>/views/admin/members/create.php" class="btn btn-secondary"><i class="fas fa-user-plus"></i> Add Member</a>
          <a href="<?= BASE_URL ?>/views/admin/transactions/overdue.php" class="btn btn-warning"><i class="fas fa-exclamation-triangle"></i> Overdue List</a>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card">
        <div class="card-header">
          Recent Transactions
          <a href="<?= BASE_URL ?>/views/admin/transactions/index.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr><th>Member</th><th>Book</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recentTx as $tx): ?>
              <tr>
                <td><?= e($tx['full_name']) ?></td>
                <td><?= e($tx['book_title']) ?></td>
                <td><?= formatDate($tx['issue_date']) ?></td>
                <td><?= formatDate($tx['due_date']) ?></td>
                <td>
                  <?php $sc = ['borrowed'=>'badge-info','returned'=>'badge-success','overdue'=>'badge-danger','lost'=>'badge-warning']; ?>
                  <span class="badge <?= $sc[$tx['status']] ?? 'badge-secondary' ?>"><?= ucfirst($tx['status']) ?></span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
