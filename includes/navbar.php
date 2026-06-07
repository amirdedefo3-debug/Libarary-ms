<?php
$user = currentUser();
$uid  = (int)($_SESSION['user_id'] ?? 0);
$db   = Database::getInstance();

// Single query: unread count + last 8 notifications
$notifStmt = $db->prepare(
    "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 8"
);
$notifStmt->execute([$uid]);
$notifications = $notifStmt->fetchAll();
$unreadCount   = array_sum(array_map(fn($n) => $n['is_read'] == 0 ? 1 : 0, $notifications));
?>
<nav class="navbar">
  <button class="navbar-toggle" id="sidebarToggle" aria-label="Toggle menu">
    <i class="fas fa-bars"></i>
  </button>

  <div class="navbar-search">
    <i class="fas fa-search"></i>
    <input type="text" placeholder="Search books, members…" id="globalSearch" autocomplete="off">
  </div>

  <div class="navbar-actions">
    <!-- Theme toggle -->
    <button class="navbar-btn" id="themeToggle" title="Toggle dark / light mode">
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

      <div class="dropdown-menu" id="notifDropdown" style="min-width:320px;right:0;">
        <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
          <strong>Notifications <?php if ($unreadCount > 0): ?><span class="badge badge-danger" style="font-size:.7rem;"><?= $unreadCount ?> new</span><?php endif; ?></strong>
          <?php if ($unreadCount > 0): ?>
            <a href="<?= BASE_URL ?>/api/notifications.php?action=mark_all_read" style="font-size:.78rem;color:var(--primary);">Mark all read</a>
          <?php endif; ?>
        </div>
        <div class="notification-list">
          <?php if (empty($notifications)): ?>
            <div style="padding:32px;text-align:center;color:var(--text-muted);">
              <i class="fas fa-bell-slash fa-2x" style="margin-bottom:8px;display:block;"></i>
              No notifications
            </div>
          <?php else:
            $icons  = ['due_reminder'=>'fa-clock','overdue'=>'fa-exclamation-triangle','reservation'=>'fa-bookmark','fine'=>'fa-money-bill','general'=>'fa-info-circle'];
            $colors = ['due_reminder'=>'yellow','overdue'=>'red','reservation'=>'blue','fine'=>'red','general'=>'purple'];
            foreach ($notifications as $notif):
              $icon  = $icons[$notif['type']]  ?? 'fa-info-circle';
              $color = $colors[$notif['type']] ?? 'purple';
          ?>
            <div class="notification-item <?= $notif['is_read'] ? '' : 'unread' ?>">
              <div class="notification-icon stat-icon <?= $color ?>">
                <i class="fas <?= $icon ?>"></i>
              </div>
              <div class="notification-text">
                <div class="notification-title"><?= e($notif['title']) ?></div>
                <div class="notification-msg"><?= e(mb_substr($notif['message'], 0, 72)) ?>…</div>
                <div class="notification-time"><?= formatDate($notif['created_at'], 'd M Y H:i') ?></div>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
        <?php
          $notifBase = match ($_SESSION['user']['role_slug'] ?? '') {
              'member'    => BASE_URL . '/views/member/notifications.php',
              default     => BASE_URL . '/views/admin/notifications/index.php',
          };
        ?>
        <div style="padding:10px 16px;border-top:1px solid var(--border);text-align:center;">
          <a href="<?= $notifBase ?>" style="font-size:.85rem;color:var(--primary);">View all notifications</a>
        </div>
      </div>
    </div>

    <!-- User menu -->
    <div class="dropdown">
      <div class="navbar-user" data-dropdown="#userDropdown" style="cursor:pointer;">
        <img src="<?= BASE_URL ?>/uploads/profiles/<?= e($user['photo'] ?? 'default.png') ?>"
             onerror="this.src='<?= BASE_URL ?>/assets/images/default.png'"
             class="avatar" alt="Profile">
        <div class="user-info">
          <div class="user-name"><?= e($user['full_name'] ?? '') ?></div>
          <div class="user-role"><?= e($user['role_name'] ?? '') ?></div>
        </div>
        <i class="fas fa-chevron-down" style="font-size:.65rem;color:var(--text-muted);margin-left:4px;"></i>
      </div>

      <div class="dropdown-menu" id="userDropdown">
        <?php
          $role        = $_SESSION['user']['role_slug'] ?? '';
          $profileUrl  = $role === 'member'
                         ? BASE_URL . '/views/member/profile.php'
                         : BASE_URL . '/views/admin/profile.php';
        ?>
        <a class="dropdown-item" href="<?= $profileUrl ?>">
          <i class="fas fa-user"></i> My Profile
        </a>
        <a class="dropdown-item" href="<?= $profileUrl ?>?tab=password">
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
