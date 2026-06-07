<?php $user = currentUser(); ?>
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

    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/dashboard.php" class="nav-link">
        <i class="fas fa-th-large"></i> Dashboard
      </a>
    </div>

    <p class="nav-section-title">Library</p>

    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="nav-link">
        <i class="fas fa-book"></i> Books
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/categories/index.php" class="nav-link">
        <i class="fas fa-tags"></i> Categories
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/authors/index.php" class="nav-link">
        <i class="fas fa-user-edit"></i> Authors
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/publishers/index.php" class="nav-link">
        <i class="fas fa-building"></i> Publishers
      </a>
    </div>

    <p class="nav-section-title">Circulation</p>

    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/transactions/index.php" class="nav-link">
        <i class="fas fa-exchange-alt"></i> Transactions
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="nav-link">
        <i class="fas fa-arrow-right"></i> Issue Book
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="nav-link">
        <i class="fas fa-arrow-left"></i> Return Book
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/reservations/index.php" class="nav-link">
        <i class="fas fa-bookmark"></i> Reservations
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/transactions/overdue.php" class="nav-link">
        <i class="fas fa-exclamation-triangle"></i> Overdue
      </a>
    </div>

    <p class="nav-section-title">Members</p>

    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/members/index.php" class="nav-link">
        <i class="fas fa-users"></i> Members
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/fines/index.php" class="nav-link">
        <i class="fas fa-money-bill"></i> Fines
      </a>
    </div>

    <?php if (hasRole('super_admin')): ?>
    <p class="nav-section-title">Administration</p>

    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/users/index.php" class="nav-link">
        <i class="fas fa-user-shield"></i> Users
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/reports/index.php" class="nav-link">
        <i class="fas fa-chart-bar"></i> Reports
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/settings/index.php" class="nav-link">
        <i class="fas fa-cog"></i> Settings
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/activity/index.php" class="nav-link">
        <i class="fas fa-history"></i> Activity Logs
      </a>
    </div>
    <div class="nav-item">
      <a href="<?= BASE_URL ?>/views/admin/backup/index.php" class="nav-link">
        <i class="fas fa-database"></i> Backup
      </a>
    </div>
    <?php endif; ?>
  </div>

  <div class="sidebar-footer">
    <a href="<?= BASE_URL ?>/logout.php" class="nav-link" style="color:#f87171;">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>
</nav>
