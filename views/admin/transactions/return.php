<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../controllers/TransactionController.php';
$ctrl = new TransactionController();
$ctrl->returnBook();
$pageTitle = 'Return Book';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Return Book</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/transactions/index.php">Transactions</a> / Return</p>
        </div>
        <a href="<?= BASE_URL ?>/views/admin/transactions/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <div style="max-width:700px;">
        <!-- Search by issue number -->
        <div class="card mb-4">
          <div class="card-header"><i class="fas fa-search" style="color:var(--primary);margin-right:8px;"></i>Find Transaction</div>
          <div class="card-body">
            <form method="GET" style="display:flex;gap:12px;">
              <input type="text" name="issue_number" value="<?= e($_GET['issue_number'] ?? '') ?>" class="form-control" placeholder="Enter Issue Number (e.g. ISS20240601ABCD12)">
              <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            </form>
          </div>
        </div>

        <?php if ($borrow): ?>
        <!-- Found transaction -->
        <div class="card mb-4">
          <div class="card-header">
            <span style="color:var(--success);"><i class="fas fa-check-circle"></i> Transaction Found</span>
          </div>
          <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
              <div>
                <small class="text-muted">Member</small>
                <p class="fw-bold"><?= e($borrow['member_name']) ?></p>
              </div>
              <div>
                <small class="text-muted">Book</small>
                <p class="fw-bold"><?= e($borrow['book_title']) ?></p>
              </div>
              <div>
                <small class="text-muted">Issue Date</small>
                <p><?= formatDate($borrow['issue_date']) ?></p>
              </div>
              <div>
                <small class="text-muted">Due Date</small>
                <?php $overdue = strtotime($borrow['due_date']) < time(); ?>
                <p class="<?= $overdue ? 'text-danger fw-bold' : '' ?>"><?= formatDate($borrow['due_date']) ?></p>
              </div>
            </div>
            <?php if ($overdue):
              $fine = calcFine($borrow['due_date']);
            ?>
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-triangle"></i>
              This book is <strong><?= $fine['days'] ?> day(s)</strong> overdue.
              Fine will be applied: <strong><?= currency($fine['amount']) ?></strong>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <form method="POST">
          <?= csrfField() ?>
          <input type="hidden" name="borrow_id" value="<?= $borrow['id'] ?>">
          <div class="card mb-4">
            <div class="card-header">Return Details</div>
            <div class="card-body">
              <div class="form-group">
                <label>Book Condition</label>
                <select name="condition" class="form-control">
                  <option value="good">Good</option>
                  <option value="damaged">Damaged</option>
                  <option value="lost">Lost</option>
                </select>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-check"></i> Confirm Return</button>
        </form>
        <?php elseif (!empty($_GET['issue_number'])): ?>
        <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> No active borrow found for that issue number.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
