<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../controllers/TransactionController.php';
$ctrl = new TransactionController();
$ctrl->index();
$pageTitle = 'Transactions';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>" data-auto-dismiss><?= e($flash['message']) ?></div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1 class="page-title">Borrow Transactions</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/dashboard.php">Dashboard</a> / Transactions</p>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Issue Book</a>
          <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Return Book</a>
        </div>
      </div>

      <!-- Status tabs -->
      <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <?php
        $statuses = ['' => 'All', 'borrowed' => 'Borrowed', 'returned' => 'Returned', 'overdue' => 'Overdue', 'lost' => 'Lost'];
        $cur = $_GET['status'] ?? '';
        foreach ($statuses as $val => $label):
        ?>
          <a href="?status=<?= $val ?><?= !empty($filters['search']) ? '&search='.urlencode($filters['search']) : '' ?>"
             class="btn btn-sm <?= $cur === $val ? 'btn-primary' : 'btn-secondary' ?>">
            <?= $label ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Search -->
      <div class="card mb-4">
        <div class="card-body" style="padding:12px 20px;">
          <form method="GET" style="display:flex;gap:12px;">
            <input type="hidden" name="status" value="<?= e($_GET['status'] ?? '') ?>">
            <div style="flex:1;position:relative;">
              <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
              <input type="text" name="search" value="<?= e($filters['search']) ?>" class="form-control" style="padding-left:36px;" placeholder="Search member, book, issue number...">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            <a href="?status=<?= e($_GET['status'] ?? '') ?>" class="btn btn-secondary"><i class="fas fa-times"></i></a>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span>Transactions <span class="badge badge-primary"><?= number_format($pagination['total']) ?></span></span>
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr>
                <th>Issue #</th>
                <th>Member</th>
                <th>Book</th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($transactions)): ?>
              <tr><td colspan="8" class="text-center text-muted" style="padding:40px;">No transactions found.</td></tr>
              <?php else: foreach ($transactions as $tx): ?>
              <tr>
                <td><small class="text-muted"><?= e($tx['issue_number']) ?></small></td>
                <td><?= e($tx['member_name']) ?><br><small class="text-muted"><?= e($tx['member_code']) ?></small></td>
                <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($tx['book_title']) ?></td>
                <td><?= formatDate($tx['issue_date']) ?></td>
                <td>
                  <?php $isOverdue = $tx['status'] === 'borrowed' && strtotime($tx['due_date']) < time(); ?>
                  <span <?= $isOverdue ? 'class="text-danger fw-bold"' : '' ?>><?= formatDate($tx['due_date']) ?></span>
                </td>
                <td><?= $tx['return_date'] ? formatDate($tx['return_date']) : '—' ?></td>
                <td>
                  <?php
                    $sc = ['borrowed'=>'badge-info','returned'=>'badge-success','overdue'=>'badge-danger','lost'=>'badge-warning'];
                    $bc = ($tx['status']==='borrowed' && $isOverdue) ? 'badge-danger' : ($sc[$tx['status']] ?? 'badge-secondary');
                    $sl = ($tx['status']==='borrowed' && $isOverdue) ? 'Overdue' : ucfirst($tx['status']);
                  ?>
                  <span class="badge <?= $bc ?>"><?= $sl ?></span>
                </td>
                <td>
                  <?php if ($tx['status'] === 'borrowed'): ?>
                    <a href="<?= BASE_URL ?>/views/admin/transactions/return.php?issue_number=<?= urlencode($tx['issue_number']) ?>" class="btn btn-sm btn-success">
                      <i class="fas fa-undo"></i> Return
                    </a>
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
        <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
          <small class="text-muted">Showing <?= $pagination['offset']+1 ?>–<?= min($pagination['offset']+$pagination['per_page'], $pagination['total']) ?> of <?= number_format($pagination['total']) ?></small>
          <div class="pagination">
            <?php for ($p = max(1, $pagination['current_page']-2); $p <= min($pagination['total_pages'], $pagination['current_page']+2); $p++): ?>
              <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" class="page-link <?= $p === $pagination['current_page'] ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
