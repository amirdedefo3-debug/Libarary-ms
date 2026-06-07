<?php
/**
 * Issue Book View
 * This file is included by TransactionController::issue() — do NOT call the controller here.
 * Variables available: $error (string), $pageTitle (string)
 */
if (!defined('BASE_PATH')) {
    // Direct access: bootstrap via controller
    require_once __DIR__ . '/../../../config/config.php';
    require_once __DIR__ . '/../../../includes/middleware.php';
    require_once __DIR__ . '/../../../controllers/TransactionController.php';
    $ctrl = new TransactionController();
    $ctrl->issue(); // controller will re-include this file and then exit
    exit;
}
$pageTitle = $pageTitle ?? 'Issue Book';
?>
<?php include BASE_PATH . '/includes/header.php'; ?>
<style>
.search-result-item {
  padding: 10px 14px;
  cursor: pointer;
  border-bottom: 1px solid var(--border);
  transition: background .15s;
}
.search-result-item:hover { background: var(--bg); }
.search-results-box {
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--bg-card);
  box-shadow: var(--shadow-md);
  max-height: 220px;
  overflow-y: auto;
  position: absolute;
  z-index: 100;
  width: 100%;
  top: 100%;
  left: 0;
}
</style>
<div class="wrapper">
  <?php include BASE_PATH . '/includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include BASE_PATH . '/includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Issue Book</h1>
          <p class="page-breadcrumb">
            <a href="<?= BASE_URL ?>/views/admin/transactions/index.php">Transactions</a> / Issue
          </p>
        </div>
        <a href="<?= BASE_URL ?>/views/admin/transactions/index.php" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
      <?php endif; ?>

      <div style="max-width:700px;">
        <form method="POST">
          <?= csrfField() ?>

          <!-- Member Search -->
          <div class="card mb-4">
            <div class="card-header">
              <i class="fas fa-user" style="color:var(--primary);margin-right:8px;"></i>Search Member
            </div>
            <div class="card-body">
              <div class="form-group" style="position:relative;">
                <label>Member Name / ID</label>
                <input type="text" id="memberSearch" class="form-control"
                       placeholder="Type member name or ID..." autocomplete="off">
                <div class="search-results-box" id="memberResults"></div>
              </div>
              <input type="hidden" name="member_id" id="member_id">
              <p class="form-text">Start typing to search. Click a result to select.</p>
            </div>
          </div>

          <!-- Book Search -->
          <div class="card mb-4">
            <div class="card-header">
              <i class="fas fa-book" style="color:var(--success);margin-right:8px;"></i>Search Book
            </div>
            <div class="card-body">
              <div class="form-group" style="position:relative;">
                <label>Book Title / ISBN</label>
                <input type="text" id="bookSearch" class="form-control"
                       placeholder="Type book title or ISBN..." autocomplete="off">
                <div class="search-results-box" id="bookResults"></div>
              </div>
              <input type="hidden" name="book_id" id="book_id">
              <p class="form-text">Only books with available copies are shown.</p>
            </div>
          </div>

          <!-- Borrow policy info -->
          <div class="card mb-4">
            <div class="card-body">
              <div style="display:flex;gap:12px;align-items:center;padding:12px;
                          background:var(--bg);border-radius:8px;">
                <i class="fas fa-info-circle" style="color:var(--info);font-size:1.1rem;"></i>
                <div>
                  <strong>Borrow period:</strong> <?= getSetting('borrow_days','14') ?> days
                  &nbsp;|&nbsp;
                  <strong>Max books per member:</strong> <?= getSetting('borrow_limit','5') ?>
                  &nbsp;|&nbsp;
                  <strong>Fine per day:</strong> <?= currency((float)getSetting('fine_per_day','5')) ?>
                </div>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" id="issueBtn">
            <i class="fas fa-check"></i> Issue Book
          </button>
        </form>
      </div>

    </div><!-- /page-content -->
  </div><!-- /main-content -->
</div><!-- /wrapper -->
<?php include BASE_PATH . '/includes/footer.php'; ?>
