<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['assistant']);

require_once __DIR__ . '/../../models/BookModel.php';
require_once __DIR__ . '/../../models/TransactionModel.php';
require_once __DIR__ . '/../../models/MemberModel.php';

$bookModel   = new BookModel();
$txModel     = new TransactionModel();
$memberModel = new MemberModel();
$db          = Database::getInstance();

$borrowedToday = $txModel->countTodayIssued();
$returnedToday = $txModel->countTodayReturned();
$activeMembers = $memberModel->countActive();
$available     = $bookModel->countAvailable();

$pageTitle = 'Assistant Librarian Dashboard';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Assistant Dashboard</h1>
          <p class="page-breadcrumb">Welcome, <?= e(currentUser()['full_name']) ?>!</p>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-book"></i></div>
          <div class="stat-info"><div class="stat-label">Available Books</div><div class="stat-value"><?= $available ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-arrow-right"></i></div>
          <div class="stat-info"><div class="stat-label">Borrowed Today</div><div class="stat-value"><?= $borrowedToday ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon cyan"><i class="fas fa-undo"></i></div>
          <div class="stat-info"><div class="stat-label">Returned Today</div><div class="stat-value"><?= $returnedToday ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-users"></i></div>
          <div class="stat-info"><div class="stat-label">Active Members</div><div class="stat-value"><?= $activeMembers ?></div></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">Quick Actions</div>
        <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;">
          <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Issue Book</a>
          <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="btn btn-success"><i class="fas fa-arrow-left"></i> Return Book</a>
          <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="btn btn-secondary"><i class="fas fa-book"></i> View Books</a>
          <a href="<?= BASE_URL ?>/views/admin/members/index.php" class="btn btn-secondary"><i class="fas fa-users"></i> View Members</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
