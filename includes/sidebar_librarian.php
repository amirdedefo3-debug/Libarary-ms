<?php $user = currentUser(); ?>
<nav class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div style="width:36px;height:36px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;">
      <i class="fas fa-book-open" style="color:#fff;font-size:1.1rem;"></i>
    </div>
    <div>
      <h1><?= e(getSetting('site_name','Library MS')) ?></h1>
      <span>Librarian Panel</span>
    </div>
  </div>

  <div class="sidebar-nav">
    <p class="nav-section-title">Main</p>
    <?php $cp = $_SERVER['PHP_SELF'] ?? ''; ?>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/librarian/dashboard.php" class="nav-link <?= strpos($cp,'librarian/dashboard') !== false ? 'active':'' ?>">
        <i class="fas fa-th-large"></i> Dashboard
      </a>
    </div>

    <p class="nav-section-title">Library</p>
    <?php
    $cp = $_SERVER['PHP_SELF'] ?? '';
    function lib_nav_link($href, $icon, $label, $cp) {
        $active = (strpos($cp, $href) !== false) ? 'active' : '';
        echo "<div class='nav-item'><a href='$href' class='nav-link $active'><i class='fas $icon'></i> $label</a></div>";
    }
    ?>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="nav-link <?= strpos($cp,'books/index') ? 'active':'' ?>">
        <i class="fas fa-book"></i> Books
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/books/create.php" class="nav-link <?= strpos($cp,'books/create') ? 'active':'' ?>">
        <i class="fas fa-plus-circle"></i> Add Book
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/categories/index.php" class="nav-link <?= strpos($cp,'categories') ? 'active':'' ?>">
        <i class="fas fa-tags"></i> Categories
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/authors/index.php" class="nav-link <?= strpos($cp,'authors') ? 'active':'' ?>">
        <i class="fas fa-user-edit"></i> Authors
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/publishers/index.php" class="nav-link <?= strpos($cp,'publishers') ? 'active':'' ?>">
        <i class="fas fa-building"></i> Publishers
      </a>
    </div>

    <p class="nav-section-title">Circulation</p>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/transactions/index.php" class="nav-link <?= strpos($cp,'transactions/index') ? 'active':'' ?>">
        <i class="fas fa-exchange-alt"></i> All Transactions
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="nav-link <?= strpos($cp,'transactions/issue') ? 'active':'' ?>">
        <i class="fas fa-arrow-circle-right"></i> Issue Book
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="nav-link <?= strpos($cp,'transactions/return') ? 'active':'' ?>">
        <i class="fas fa-arrow-circle-left"></i> Return Book
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/transactions/overdue.php" class="nav-link <?= strpos($cp,'transactions/overdue') ? 'active':'' ?>">
        <i class="fas fa-exclamation-triangle"></i> Overdue Books
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/reservations/index.php" class="nav-link <?= strpos($cp,'reservations') ? 'active':'' ?>">
        <i class="fas fa-bookmark"></i> Reservations
      </a>
    </div>

    <p class="nav-section-title">Members</p>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/members/index.php" class="nav-link <?= strpos($cp,'members/index') ? 'active':'' ?>">
        <i class="fas fa-users"></i> Members
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/members/create.php" class="nav-link <?= strpos($cp,'members/create') ? 'active':'' ?>">
        <i class="fas fa-user-plus"></i> Add Member
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/fines/index.php" class="nav-link <?= strpos($cp,'fines') ? 'active':'' ?>">
        <i class="fas fa-money-bill-wave"></i> Fines
      </a>
    </div>

    <p class="nav-section-title">Reports</p>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/reports/index.php" class="nav-link <?= strpos($cp,'reports') ? 'active':'' ?>">
        <i class="fas fa-chart-bar"></i> Reports
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
        <div style="font-size:.7rem;color:rgba(199,210,254,.6);">Librarian</div>
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
