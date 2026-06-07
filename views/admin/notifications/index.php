<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
requireLogin();

$db = Database::getInstance();
// Mark all read
$db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$_SESSION['user_id']]);

$page  = max(1, (int)($_GET['page'] ?? 1));
$per   = 20;
$off   = ($page - 1) * $per;
$total = (int)$db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=?")->execute([$_SESSION['user_id']]) ? (function() use ($db) { $s=$db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=?"); $s->execute([$_SESSION['user_id']]); return (int)$s->fetchColumn(); })() : 0;

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT $per OFFSET $off");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();
$pagination    = paginate($total, $per, $page);
$pageTitle     = 'Notifications';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <h1 class="page-title">Notifications</h1>
      </div>
      <div class="card">
        <div class="card-header">All Notifications</div>
        <?php if (empty($notifications)): ?>
          <div class="card-body text-center text-muted" style="padding:48px;">
            <i class="fas fa-bell-slash fa-3x" style="margin-bottom:12px;color:var(--border);"></i>
            <p>No notifications yet.</p>
          </div>
        <?php else: foreach ($notifications as $n):
          $icons  = ['due_reminder'=>'fa-clock','overdue'=>'fa-exclamation','reservation'=>'fa-bookmark','fine'=>'fa-money-bill','general'=>'fa-info-circle'];
          $colors = ['due_reminder'=>'yellow','overdue'=>'red','reservation'=>'blue','fine'=>'red','general'=>'purple'];
        ?>
        <div class="notification-item" style="border-bottom:1px solid var(--border);">
          <div class="notification-icon stat-icon <?= $colors[$n['type']] ?? 'purple' ?>">
            <i class="fas <?= $icons[$n['type']] ?? 'fa-info' ?>"></i>
          </div>
          <div class="notification-text">
            <div class="notification-title"><?= e($n['title']) ?></div>
            <div class="notification-msg"><?= e($n['message']) ?></div>
            <div class="notification-time"><?= formatDate($n['created_at'], 'd M Y H:i') ?></div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
