<?php
$user = currentUser();
// Count unread notifications for badge
$db = Database::getInstance();
$nStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$nStmt->execute([$_SESSION['user_id']]);
$unreadNotif = (int)$nStmt->fetchColumn();

// Count active borrows for badge
$member = $_SESSION['member'] ?? null;
$activeBorrowCount = 0;
if ($member) {
    $bStmt = $db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE member_id=? AND status='borrowed'");
    $bStmt->execute([$member['id']]);
    $activeBorrowCount = (int)$bStmt->fetchColumn();
}

// Count pending fines
$pendingFineCount = 0;
if ($member) {
    $fStmt = $db->prepare("SELECT COUNT(*) FROM fines WHERE member_id=? AND status='pending'");
    $fStmt->execute([$member['id']]);
    $pendingFineCount = (int)$fStmt->fetchColumn();
}
?>
<nav class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div style="width:36px;height:36px;background:var(--secondary);border-radius:8px;display:flex;align-items:center;justify-content:center;">
      <i class="fas fa-book-reader" style="color:#fff;font-size:1.1rem;"></i>
    </div>
    <div>
      <h1><?= e(getSetting('site_name','Library MS')) ?></h1>
      <span>Member Portal</span>
    </div>
  </div>

  <div class="sidebar-nav">
    <p class="nav-section-title">My Space</p>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/member/dashboard.php" class="nav-link">
        <i class="fas fa-home"></i> Dashboard
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/member/search.php" class="nav-link">
        <i class="fas fa-search"></i> Search Books
      </a>
    </div>

    <p class="nav-section-title">My Library</p>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/member/borrows.php" class="nav-link">
        <i class="fas fa-book-open"></i> My Borrows
        <?php if ($activeBorrowCount > 0): ?>
          <span class="nav-badge"><?= $activeBorrowCount ?></span>
        <?php endif; ?>
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/member/reservations.php" class="nav-link">
        <i class="fas fa-bookmark"></i> My Reservations
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/member/fines.php" class="nav-link">
        <i class="fas fa-money-bill-wave"></i> My Fines
        <?php if ($pendingFineCount > 0): ?>
          <span class="nav-badge"><?= $pendingFineCount ?></span>
        <?php endif; ?>
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/member/notifications.php" class="nav-link">
        <i class="fas fa-bell"></i> Notifications
        <?php if ($unreadNotif > 0): ?>
          <span class="nav-badge"><?= $unreadNotif ?></span>
        <?php endif; ?>
      </a>
    </div>

    <p class="nav-section-title">Account</p>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/member/profile.php" class="nav-link">
        <i class="fas fa-user-circle"></i> My Profile
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/member/membership_card.php" class="nav-link">
        <i class="fas fa-id-card"></i> Membership Card
      </a>
    </div>
  </div>

  <div class="sidebar-footer">
    <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:8px;margin-bottom:8px;">
      <img src="<?= BASE_URL ?>/uploads/profiles/<?= e($user['photo'] ?? 'default.png') ?>"
           onerror="this.src='<?= BASE_URL ?>/assets/images/default.png'"
           style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.2);">
      <div style="min-width:0;">
        <div style="font-size:.8rem;font-weight:600;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($user['full_name'] ?? '') ?></div>
        <div style="font-size:.7rem;color:rgba(199,210,254,.6);">Member</div>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/logout.php" class="nav-link" style="color:#f87171;">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>
</nav>
