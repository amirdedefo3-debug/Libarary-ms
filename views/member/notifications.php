<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['member']);

require_once __DIR__ . '/../../models/MemberModel.php';

$memberModel = new MemberModel();
$db          = Database::getInstance();

$member = $memberModel->findByUserId($_SESSION['user_id']);
if (!$member) redirect(BASE_URL . '/unauthorized.php');
$_SESSION['member'] = $member;

// Mark all as read on page load
$db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$_SESSION['user_id']]);

$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 15;
$offset = ($page - 1) * $per;

$total = (int)$db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=?")->execute([$_SESSION['user_id']])
    ? (function() use ($db) {
        $s = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=?");
        $s->execute([$_SESSION['user_id']]);
        return (int)$s->fetchColumn();
      })()
    : 0;

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT $per OFFSET $offset");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
$pagination    = paginate($total, $per, $page);

$iconMap  = ['due_reminder'=>'fa-clock','overdue'=>'fa-exclamation-triangle','reservation'=>'fa-bookmark','fine'=>'fa-money-bill-wave','general'=>'fa-info-circle'];
$colorMap = ['due_reminder'=>'yellow','overdue'=>'red','reservation'=>'blue','fine'=>'red','general'=>'purple'];

$pageTitle = 'Notifications';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_member.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">My Notifications</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/member/dashboard.php">Dashboard</a> / Notifications</p>
        </div>
        <?php if ($total > 0): ?>
          <span class="badge badge-primary" style="font-size:.85rem;padding:6px 14px;"><?= $total ?> total</span>
        <?php endif; ?>
      </div>

      <div class="card">
        <?php if (empty($notifications)): ?>
          <div class="card-body text-center" style="padding:60px 20px;">
            <i class="fas fa-bell-slash fa-3x" style="color:var(--border);margin-bottom:16px;"></i>
            <h3>No Notifications</h3>
            <p class="text-muted">You're all caught up!</p>
          </div>
        <?php else: foreach ($notifications as $n):
          $ic = $iconMap[$n['type']] ?? 'fa-info-circle';
          $cl = $colorMap[$n['type']] ?? 'purple';
        ?>
        <div class="notification-item" style="padding:16px 20px;">
          <div class="notification-icon stat-icon <?= $cl ?>">
            <i class="fas <?= $ic ?>"></i>
          </div>
          <div class="notification-text" style="flex:1;">
            <div class="notification-title" style="font-size:.95rem;"><?= e($n['title']) ?></div>
            <div class="notification-msg" style="font-size:.85rem;margin-top:4px;line-height:1.5;"><?= e($n['message']) ?></div>
            <div class="notification-time" style="margin-top:6px;">
              <i class="fas fa-clock" style="margin-right:4px;"></i><?= formatDate($n['created_at'],'d M Y, H:i') ?>
            </div>
          </div>
          <div>
            <span class="badge <?= [
              'due_reminder'=>'badge-warning',
              'overdue'=>'badge-danger',
              'reservation'=>'badge-info',
              'fine'=>'badge-danger',
              'general'=>'badge-secondary',
            ][$n['type']] ?? 'badge-secondary' ?>" style="text-transform:capitalize;">
              <?= str_replace('_',' ', $n['type']) ?>
            </span>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>

      <?php if ($pagination['total_pages'] > 1): ?>
      <div style="display:flex;justify-content:center;margin-top:20px;">
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
<?php include __DIR__ . '/../../includes/footer.php'; ?>
