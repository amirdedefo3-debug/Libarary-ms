<?php
$user = currentUser();
$cp   = $_SERVER['PHP_SELF'] ?? '';
function _nav($href, $icon, $label) {
    global $cp;
    $active = (strpos($cp, parse_url($href, PHP_URL_PATH)) !== false) ? 'active' : '';
    echo "<div class='nav-item'><a href='" . htmlspecialchars($href) . "' class='nav-link $active'>"
       . "<i class='fas $icon'></i> $label</a></div>";
}
?>
<nav class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div style="width:36px;height:36px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;">
      <i class="fas fa-book-open" style="color:#fff;font-size:1.1rem;"></i>
    </div>
    <div>
      <h1><?= e(getSetting('site_name','Library MS')) ?></h1>
      <span><?= hasRole('super_admin') ? 'Super Admin Panel' : 'Management System' ?></span>
    </div>
  </div>

  <div class="sidebar-nav">
    <p class="nav-section-title">Main</p>
    <?php _nav(BASE_URL.'/views/admin/dashboard.php','fa-th-large','Dashboard'); ?>

    <p class="nav-section-title">Library</p>
    <?php _nav(BASE_URL.'/views/admin/books/index.php','fa-book','Books'); ?>
    <?php _nav(BASE_URL.'/views/admin/categories/index.php','fa-tags','Categories'); ?>
    <?php _nav(BASE_URL.'/views/admin/authors/index.php','fa-user-edit','Authors'); ?>
    <?php _nav(BASE_URL.'/views/admin/publishers/index.php','fa-building','Publishers'); ?>

    <p class="nav-section-title">Circulation</p>
    <?php _nav(BASE_URL.'/views/admin/transactions/index.php','fa-exchange-alt','Transactions'); ?>
    <?php _nav(BASE_URL.'/views/admin/transactions/issue.php','fa-arrow-right','Issue Book'); ?>
    <?php _nav(BASE_URL.'/views/admin/transactions/return.php','fa-arrow-left','Return Book'); ?>
    <?php _nav(BASE_URL.'/views/admin/reservations/index.php','fa-bookmark','Reservations'); ?>
    <?php _nav(BASE_URL.'/views/admin/transactions/overdue.php','fa-exclamation-triangle','Overdue'); ?>

    <p class="nav-section-title">Members</p>
    <?php _nav(BASE_URL.'/views/admin/members/index.php','fa-users','Members'); ?>
    <?php _nav(BASE_URL.'/views/admin/fines/index.php','fa-money-bill','Fines'); ?>

    <?php if (hasRole('super_admin')): ?>
    <p class="nav-section-title">Administration</p>
    <?php _nav(BASE_URL.'/views/admin/users/index.php','fa-user-shield','Users'); ?>
    <?php _nav(BASE_URL.'/views/admin/reports/index.php','fa-chart-bar','Reports'); ?>
    <?php _nav(BASE_URL.'/views/admin/settings/index.php','fa-cog','Settings'); ?>
    <?php _nav(BASE_URL.'/views/admin/activity/index.php','fa-history','Activity Logs'); ?>
    <?php _nav(BASE_URL.'/views/admin/backup/index.php','fa-database','Backup'); ?>
    <?php endif; ?>
  </div>

  <div class="sidebar-footer">
    <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:8px;margin-bottom:8px;">
      <img src="<?= BASE_URL ?>/uploads/profiles/<?= e($user['photo'] ?? 'default.png') ?>"
           onerror="this.src='<?= BASE_URL ?>/assets/images/default.png'"
           style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.2);">
      <div style="min-width:0;">
        <div style="font-size:.8rem;font-weight:600;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($user['full_name'] ?? '') ?></div>
        <div style="font-size:.7rem;color:rgba(199,210,254,.6);"><?= ucwords(str_replace('_',' ',$user['role_slug'] ?? '')) ?></div>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/views/admin/profile.php" class="nav-link">
      <i class="fas fa-user-cog"></i> My Profile
    </a>
    <a href="<?= BASE_URL ?>/logout.php" class="nav-link" style="color:#f87171;">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>
</nav>
