<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
requirePermission('fines.manage');
require_once __DIR__ . '/../../../models/FineModel.php';

$fineModel = new FineModel();
$page      = max(1, (int)($_GET['page'] ?? 1));
$status    = $_GET['status'] ?? '';
$result    = $fineModel->getAll($page, 20, $status);
$fines     = $result['data'];
$pagination = paginate($result['total'], 20, $page);

// Handle payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_fine_id'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid token.');
    } else {
        $fineId = (int)$_POST['pay_fine_id'];
        $amount = (float)$_POST['amount'];
        $fineModel->payFine($fineId, $amount, $_SESSION['user_id'], $_POST['method'] ?? 'cash');
        logActivity('pay_fine', 'fines', "Paid fine #$fineId");
        setFlash('success', 'Fine payment recorded.');
    }
    header('Location: ' . BASE_URL . '/views/admin/fines/index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['waive_fine_id'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $fineModel->waiveFine((int)$_POST['waive_fine_id']);
        setFlash('success', 'Fine waived.');
    }
    header('Location: ' . BASE_URL . '/views/admin/fines/index.php');
    exit;
}

$pageTitle = 'Fines';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>" data-auto-dismiss><?= e($flash['message']) ?></div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1 class="page-title">Fine Management</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/dashboard.php">Dashboard</a> / Fines</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          <strong>Collected: <span class="text-success"><?= currency($fineModel->totalCollected()) ?></span></strong>
          &nbsp;|&nbsp;
          <strong>Pending: <span class="text-danger"><?= currency($fineModel->totalPending()) ?></span></strong>
        </div>
      </div>

      <!-- Status tabs -->
      <div style="display:flex;gap:8px;margin-bottom:16px;">
        <?php foreach (['' => 'All', 'pending' => 'Pending', 'paid' => 'Paid', 'waived' => 'Waived'] as $val => $label): ?>
          <a href="?status=<?= $val ?>" class="btn btn-sm <?= $status === $val ? 'btn-primary' : 'btn-secondary' ?>"><?= $label ?></a>
        <?php endforeach; ?>
      </div>

      <div class="card">
        <div class="card-header">
          Fines <span class="badge badge-primary"><?= number_format($pagination['total']) ?></span>
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Member</th>
                <th>Book</th>
                <th>Due Date</th>
                <th>Days Overdue</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($fines)): ?>
              <tr><td colspan="8" class="text-center text-muted" style="padding:40px;">No fines found.</td></tr>
              <?php else: foreach ($fines as $i => $fine): ?>
              <tr>
                <td><?= $pagination['offset']+$i+1 ?></td>
                <td><?= e($fine['member_name']) ?><br><small class="text-muted"><?= e($fine['member_code']) ?></small></td>
                <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($fine['book_title']) ?></td>
                <td><?= formatDate($fine['due_date']) ?></td>
                <td><span class="badge badge-danger"><?= $fine['days_overdue'] ?> days</span></td>
                <td class="fw-bold text-danger"><?= currency($fine['amount']) ?></td>
                <td>
                  <span class="badge <?= $fine['status']==='paid'?'badge-success':($fine['status']==='waived'?'badge-warning':'badge-danger') ?>">
                    <?= ucfirst($fine['status']) ?>
                  </span>
                </td>
                <td>
                  <?php if ($fine['status'] === 'pending'): ?>
                  <div class="d-flex gap-2">
                    <!-- Pay -->
                    <form method="POST" style="display:inline;">
                      <?= csrfField() ?>
                      <input type="hidden" name="pay_fine_id" value="<?= $fine['id'] ?>">
                      <input type="hidden" name="amount" value="<?= $fine['amount'] ?>">
                      <input type="hidden" name="method" value="cash">
                      <button type="submit" class="btn btn-sm btn-success" data-confirm="Confirm payment of <?= currency($fine['amount']) ?>?">
                        <i class="fas fa-check"></i> Pay
                      </button>
                    </form>
                    <!-- Waive -->
                    <form method="POST" style="display:inline;">
                      <?= csrfField() ?>
                      <input type="hidden" name="waive_fine_id" value="<?= $fine['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-warning" data-confirm="Waive this fine?">
                        <i class="fas fa-times"></i> Waive
                      </button>
                    </form>
                  </div>
                  <?php else: ?>
                    <span class="text-muted" style="font-size:.8rem;">—</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
          <small class="text-muted">Showing <?= $pagination['offset']+1 ?>–<?= min($pagination['offset']+$pagination['per_page'],$pagination['total']) ?> of <?= number_format($pagination['total']) ?></small>
          <div class="pagination">
            <?php for ($p = max(1,$pagination['current_page']-2); $p <= min($pagination['total_pages'],$pagination['current_page']+2); $p++): ?>
              <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="page-link <?= $p===$pagination['current_page']?'active':'' ?>"><?= $p ?></a>
            <?php endfor; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
