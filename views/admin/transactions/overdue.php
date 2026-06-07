<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../controllers/TransactionController.php';
$ctrl = new TransactionController();
$ctrl->overdue();
$pageTitle = 'Overdue Books';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Overdue Books</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/transactions/index.php">Transactions</a> / Overdue</p>
        </div>
      </div>

      <?php if (empty($transactions)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> No overdue books right now!</div>
      <?php else: ?>
      <div class="card">
        <div class="card-header"><span class="text-danger"><i class="fas fa-exclamation-triangle"></i> <?= count($transactions) ?> Overdue Books</span></div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr><th>Issue #</th><th>Member</th><th>Book</th><th>Issue Date</th><th>Due Date</th><th>Days Overdue</th><th>Est. Fine</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($transactions as $tx):
                $daysOver = (int)floor((time() - strtotime($tx['due_date'])) / 86400);
                $fpd      = (float)getSetting('fine_per_day', '5');
                $maxFine  = (float)getSetting('max_fine', '500');
                $estFine  = min($daysOver * $fpd, $maxFine);
              ?>
              <tr>
                <td><small><?= e($tx['issue_number']) ?></small></td>
                <td><?= e($tx['full_name']) ?><br><small class="text-muted"><?= e($tx['member_code']) ?></small></td>
                <td><?= e($tx['book_title']) ?></td>
                <td><?= formatDate($tx['issue_date']) ?></td>
                <td class="text-danger fw-bold"><?= formatDate($tx['due_date']) ?></td>
                <td><span class="badge badge-danger"><?= $daysOver ?> days</span></td>
                <td class="text-danger fw-bold"><?= currency($estFine) ?></td>
                <td>
                  <a href="<?= BASE_URL ?>/views/admin/transactions/return.php?issue_number=<?= urlencode($tx['issue_number']) ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-undo"></i> Return
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
