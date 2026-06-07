<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
middleware(['super_admin']);

$db   = Database::getInstance();
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 30;
$off  = ($page - 1) * $per;

$total = (int)$db->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
$logs  = $db->query(
    "SELECT al.*, u.full_name, u.email
     FROM activity_logs al LEFT JOIN users u ON al.user_id=u.id
     ORDER BY al.created_at DESC LIMIT $per OFFSET $off"
)->fetchAll();

$pagination = paginate($total, $per, $page);
$pageTitle  = 'Activity Logs';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <h1 class="page-title">Activity Logs</h1>
      </div>
      <div class="card">
        <div class="card-header">System Activity <span class="badge badge-primary"><?= number_format($total) ?></span></div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead><tr><th>#</th><th>User</th><th>Action</th><th>Module</th><th>Description</th><th>IP</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach ($logs as $i => $log): ?>
              <tr>
                <td><?= $pagination['offset']+$i+1 ?></td>
                <td><?= e($log['full_name'] ?? 'System') ?><br><small class="text-muted"><?= e($log['email'] ?? '') ?></small></td>
                <td><span class="badge badge-info"><?= e($log['action']) ?></span></td>
                <td><small><?= e($log['module'] ?? '') ?></small></td>
                <td><small><?= e(substr($log['description'] ?? '', 0, 80)) ?></small></td>
                <td><small class="text-muted"><?= e($log['ip_address'] ?? '') ?></small></td>
                <td><small><?= formatDate($log['created_at'], 'd M Y H:i') ?></small></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center;">
          <small class="text-muted">Showing <?= $pagination['offset']+1 ?>–<?= min($pagination['offset']+$per,$total) ?> of <?= number_format($total) ?></small>
          <div class="pagination">
            <?php for ($p = max(1,$pagination['current_page']-2); $p <= min($pagination['total_pages'],$pagination['current_page']+2); $p++): ?>
              <a href="?page=<?= $p ?>" class="page-link <?= $p===$pagination['current_page']?'active':'' ?>"><?= $p ?></a>
            <?php endfor; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
