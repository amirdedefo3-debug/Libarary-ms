<?php
$user = currentUser();
// Unread notifications count
$db = Database::getInstance();
$notifStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$notifStmt->execute([$_SESSION['user_id']]);
$unreadCount = (int)$notifStmt->fetchColumn();

// Recent notifications
$notifListStmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 8");
$notifListStmt->execute([$_SESSION['user_id']]);
$notifications = $notifListStmt->fetchAll();
?>
<nav class="navbar">
  <button class="navbar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
  </button>

  <div class="navbar-search">
    <i class="fas fa-search"></i>
    <input type="text" placeholder="Search books, members..." id="globalSearch" autocomplete="off">
  </div>

  <div class="navbar-actions">
    <!-- Theme toggle -->
    <button class="navbar-btn" id="themeToggle" title="Toggle theme">
      <i class="fas fa-moon"></i>
    </button>

    <!-- Notifications -->
    <div class="dropdown">
      <button class="navbar-btn" data-dropdown="#notifDropdown" title="Notifications">
        <i class="fas fa-bell"></i>
        <?php if ($unreadCount > 0): ?>
          <span class="badge-dot"></span>
        <?php endif; ?>
      </button>
      <div class="dropdown-menu" id="notifDropdown" style="min-width:320px;">
        <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
          <strong>Notifications</strong>
          <?php if ($unreadCount > 0): ?>
            <a href="<?= BASE_URL ?>/api/notifications.php?action=mark_all_read" style="font-size:.78rem;">Mark all read</a>
          <?php endif; ?>
        </div>
        <div class="notification-list">
          <?php if (empty($notifications)): ?>
            <div style="padding:24px;text-align:center;color:var(--text-muted);">
              <i class="fas fa-bell-slash fa-2x" style="margin-bottom:8px;"></i>
              <p>No notifications</p>
            </div>
          <?php else: foreach ($notifications as $notif):
            $icons = ['due_reminder'=>'fa-clock','overdue'=>'fa-exclamation','reservation'=>'fa-bookmark','fine'=>'fa-money-bill','general'=>'fa-info'];
            $colors = ['due_reminder'=>'yellow','overdue'=>'red','reservation'=>'blue','fine'=>'red','general'=>'purple'];
            $icon  = $icons[$notif['type']] ?? 'fa-info';
            $color = $colors[$notif['type']] ?? 'purple';
          ?>
            <div class="notification-item <?= $notif['is_read'] ? '' : 'unread' ?>">
              <div class="notification-icon stat-icon <?= $color ?>">
                <i class="fas <?= $icon ?>"></i>
              </div>
              <div class="notification-text">
                <div class="notification-title"><?= e($notif['title']) ?></div>
                <div class="notification-msg"><?= e(substr($notif['message'],0,80)) ?>...</div>
                <div class="notification-time"><?= formatDate($notif['created_at'], 'd M Y H:i') ?></div>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
        <div style="padding:10px 16px;border-top:1px solid var(--border);text-align:center;">
          <a href="<?= BASE_URL ?>/views/admin/notifications/index.php" style="font-size:.85rem;">View all</a>
        </div>
      </div>
    </div>

    <!-- User menu -->
    <div class="dropdown">
      <div class="navbar-user" data-dropdown="#userDropdown">
        <img src="<?= BASE_URL ?>/uploads/profiles/<?= e($user['photo'] ?? 'default.png') ?>" alt="Profile" class="avatar">
        <div class="user-info">
          <div class="user-name"><?= e($user['full_name'] ?? '') ?></div>
          <div class="user-role"><?= e($user['role_name'] ?? '') ?></div>
        </div>
        <i class="fas fa-chevron-down" style="font-size:.7rem;color:var(--text-muted);margin-left:4px;"></i>
      </div>
      <div class="dropdown-menu" id="userDropdown">
        <a class="dropdown-item" href="<?= BASE_URL ?>/views/<?= in_array($_SESSION['user']['role_slug'] ?? '', ['member']) ? 'member' : 'admin' ?>/profile.php">
          <i class="fas fa-user"></i> My Profile
        </a>
        <a class="dropdown-item" href="<?= BASE_URL ?>/views/<?= in_array($_SESSION['user']['role_slug'] ?? '', ['member']) ? 'member' : 'admin' ?>/profile.php?tab=password">
          <i class="fas fa-lock"></i> Change Password
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="<?= BASE_URL ?>/logout.php" style="color:var(--danger);">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>
  </div>
</nav>
