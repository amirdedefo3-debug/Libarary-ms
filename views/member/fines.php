<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['member']);

require_once __DIR__ . '/../../models/MemberModel.php';
require_once __DIR__ . '/../../models/FineModel.php';

$memberModel = new MemberModel();
$fineModel   = new FineModel();
$db          = Database::getInstance();

$member = $memberModel->findByUserId($_SESSION['user_id']);
if (!$member) redirect(BASE_URL . '/unauthorized.php');
$_SESSION['member'] = $member;

$fines = $fineModel->getMemberFines($member['id']);

$totalPending = array_sum(array_column(array_filter($fines, fn($f) => $f['status']==='pending'), 'amount'));
$totalPaid    = array_sum(array_column(array_filter($fines, fn($f) => $f['status']==='paid'), 'amount'));
$totalWaived  = array_sum(array_column(array_filter($fines, fn($f) => $f['status']==='waived'), 'amount'));

$pageTitle = 'My Fines';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_member.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">My Fines</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/member/dashboard.php">Dashboard</a> / Fines</p>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));margin-bottom:24px;">
        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-exclamation-circle"></i></div>
          <div class="stat-info">
            <div class="stat-label">Pending Fines</div>
            <div class="stat-value"><?= currency($totalPending) ?></div>
            <div class="stat-change down"><?= count(array_filter($fines, fn($f)=>$f['status']==='pending')) ?> unpaid fine(s)</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
          <div class="stat-info">
            <div class="stat-label">Total Paid</div>
            <div class="stat-value"><?= currency($totalPaid) ?></div>
            <div class="stat-change up">Cleared</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow"><i class="fas fa-hand-holding-heart"></i></div>
          <div class="stat-info">
            <div class="stat-label">Waived</div>
            <div class="stat-value"><?= currency($totalWaived) ?></div>
            <div class="stat-change text-muted">By librarian</div>
          </div>
        </div>
      </div>

      <?php if ($totalPending > 0): ?>
      <div class="alert alert-danger" style="margin-bottom:20px;">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>You have <?= currency($totalPending) ?> in unpaid fines.</strong>
        Please visit the library counter to settle your dues.
      </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">Fine History <span class="badge badge-primary"><?= count($fines) ?></span></div>
        <?php if (empty($fines)): ?>
          <div class="card-body text-center" style="padding:60px 20px;">
            <i class="fas fa-smile fa-3x" style="color:var(--success);margin-bottom:16px;"></i>
            <h3>No Fines!</h3>
            <p class="text-muted">You have no fine records. Keep returning books on time!</p>
          </div>
        <?php else: ?>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr><th>Book</th><th>Due Date</th><th>Days Overdue</th><th>Fine Amount</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php foreach ($fines as $fine): ?>
              <tr>
                <td>
                  <strong style="font-size:.88rem;"><?= e($fine['book_title']) ?></strong><br>
                  <small class="text-muted">Due: <?= formatDate($fine['due_date']) ?></small>
                </td>
                <td><?= formatDate($fine['due_date']) ?></td>
                <td>
                  <span class="badge badge-danger"><?= $fine['days_overdue'] ?> day<?= $fine['days_overdue']!=1?'s':'' ?></span>
                </td>
                <td class="fw-bold <?= $fine['status']==='pending'?'text-danger':'' ?>">
                  <?= currency($fine['amount']) ?>
                </td>
                <td>
                  <span class="badge <?= $fine['status']==='paid'?'badge-success':($fine['status']==='waived'?'badge-warning':'badge-danger') ?>">
                    <?= ucfirst($fine['status']) ?>
                  </span>
                  <?php if ($fine['status'] === 'pending'): ?>
                    <div style="font-size:.72rem;color:var(--text-muted);margin-top:2px;">Visit library to pay</div>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Fine Rules Info -->
      <div class="card" style="margin-top:16px;">
        <div class="card-header"><i class="fas fa-info-circle" style="color:var(--info);margin-right:8px;"></i>Fine Policy</div>
        <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">
          <div style="padding:12px;background:var(--bg);border-radius:8px;">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:4px;">Fine Per Day</div>
            <div style="font-weight:700;font-size:1.1rem;color:var(--danger);"><?= currency((float)getSetting('fine_per_day','5')) ?></div>
          </div>
          <div style="padding:12px;background:var(--bg);border-radius:8px;">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:4px;">Maximum Fine</div>
            <div style="font-weight:700;font-size:1.1rem;color:var(--danger);"><?= currency((float)getSetting('max_fine','500')) ?></div>
          </div>
          <div style="padding:12px;background:var(--bg);border-radius:8px;">
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:4px;">Borrow Period</div>
            <div style="font-weight:700;font-size:1.1rem;"><?= getSetting('borrow_days','14') ?> days</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
