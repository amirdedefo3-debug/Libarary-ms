<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['assistant']);

require_once __DIR__ . '/../../models/BookModel.php';
require_once __DIR__ . '/../../models/TransactionModel.php';
require_once __DIR__ . '/../../models/MemberModel.php';

$bookModel   = new BookModel();
$txModel     = new TransactionModel();
$memberModel = new MemberModel();
$db          = Database::getInstance();

// ── Stats ──────────────────────────────────────────────────
$availableBooks = $bookModel->countAvailable();
$totalBooks     = $bookModel->count();
$borrowedToday  = $txModel->countTodayIssued();
$returnedToday  = $txModel->countTodayReturned();
$activeMembers  = $memberModel->countActive();
$overdueCount   = (int)$db->query("SELECT COUNT(*) FROM borrow_transactions WHERE status='borrowed' AND due_date < CURDATE()")->fetchColumn();

// ── Today's issued books ───────────────────────────────────
$todayIssued = $db->query(
    "SELECT bt.issue_number, bt.issue_date, bt.due_date,
            b.title AS book_title, u.full_name AS member_name
     FROM borrow_transactions bt
     JOIN books b   ON bt.book_id   = b.id
     JOIN members m ON bt.member_id = m.id
     JOIN users u   ON m.user_id    = u.id
     WHERE DATE(bt.issue_date) = CURDATE()
     ORDER BY bt.created_at DESC LIMIT 8"
)->fetchAll();

// ── Today's returned books ─────────────────────────────────
$todayReturned = $db->query(
    "SELECT bt.issue_number, bt.return_date,
            b.title AS book_title, u.full_name AS member_name
     FROM borrow_transactions bt
     JOIN books b   ON bt.book_id   = b.id
     JOIN members m ON bt.member_id = m.id
     JOIN users u   ON m.user_id    = u.id
     WHERE DATE(bt.return_date) = CURDATE()
     ORDER BY bt.created_at DESC LIMIT 8"
)->fetchAll();

// ── Recently added books (things assistant can add) ────────
$recentBooks = $db->query(
    "SELECT b.id, b.title, b.cover_image, b.available_quantity, a.name AS author_name,
            c.name AS category_name, b.created_at
     FROM books b
     LEFT JOIN authors a    ON b.author_id    = a.id
     LEFT JOIN categories c ON b.category_id  = c.id
     ORDER BY b.created_at DESC LIMIT 5"
)->fetchAll();

$pageTitle = 'Assistant Librarian Dashboard';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_assistant.php'; ?>

  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="page-content">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>" data-auto-dismiss>
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>

      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Assistant Dashboard</h1>
          <p class="page-breadcrumb">
            Welcome back, <strong><?= e(currentUser()['full_name']) ?></strong>!
            &nbsp;·&nbsp; <?= date('l, d F Y') ?>
          </p>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="btn btn-primary">
            <i class="fas fa-arrow-circle-right"></i> Issue Book
          </a>
          <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="btn btn-success">
            <i class="fas fa-arrow-circle-left"></i> Return Book
          </a>
        </div>
      </div>

      <!-- Permission Notice -->
      <div class="alert alert-info" style="margin-bottom:20px;">
        <i class="fas fa-info-circle"></i>
        <strong>Your permissions:</strong> You can view books, add books, update books, issue books, return books, and manage members.
        You cannot delete books, manage users, or change system settings.
      </div>

      <!-- ── Stat Cards ── -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-book"></i></div>
          <div class="stat-info">
            <div class="stat-label">Available Books</div>
            <div class="stat-value"><?= number_format($availableBooks) ?></div>
            <div class="stat-change text-muted"><?= number_format($totalBooks) ?> total in system</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-arrow-circle-right"></i></div>
          <div class="stat-info">
            <div class="stat-label">Borrowed Today</div>
            <div class="stat-value"><?= number_format($borrowedToday) ?></div>
            <div class="stat-change text-muted"><i class="fas fa-calendar-day"></i> Today</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-arrow-circle-left"></i></div>
          <div class="stat-info">
            <div class="stat-label">Returned Today</div>
            <div class="stat-value"><?= number_format($returnedToday) ?></div>
            <div class="stat-change text-muted"><i class="fas fa-calendar-day"></i> Today</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon cyan"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <div class="stat-label">Active Members</div>
            <div class="stat-value"><?= number_format($activeMembers) ?></div>
            <div class="stat-change text-muted">Registered members</div>
          </div>
        </div>

        <?php if ($overdueCount > 0): ?>
        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
          <div class="stat-info">
            <div class="stat-label">Overdue Books</div>
            <div class="stat-value"><?= number_format($overdueCount) ?></div>
            <div class="stat-change down">Needs attention</div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- ── Quick Actions ── -->
      <div class="card mb-4">
        <div class="card-header"><i class="fas fa-bolt" style="color:var(--warning);margin-right:8px;"></i>Quick Actions</div>
        <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;">
          <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="btn btn-primary">
            <i class="fas fa-arrow-circle-right"></i> Issue Book
          </a>
          <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="btn btn-success">
            <i class="fas fa-arrow-circle-left"></i> Return Book
          </a>
          <a href="<?= BASE_URL ?>/views/admin/books/create.php" class="btn btn-secondary">
            <i class="fas fa-plus"></i> Add Book
          </a>
          <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="btn btn-secondary">
            <i class="fas fa-book"></i> Browse Books
          </a>
          <a href="<?= BASE_URL ?>/views/admin/members/create.php" class="btn btn-secondary">
            <i class="fas fa-user-plus"></i> Add Member
          </a>
          <a href="<?= BASE_URL ?>/views/admin/members/index.php" class="btn btn-secondary">
            <i class="fas fa-users"></i> Browse Members
          </a>
          <a href="<?= BASE_URL ?>/views/admin/transactions/index.php" class="btn btn-secondary">
            <i class="fas fa-list"></i> All Transactions
          </a>
        </div>
      </div>

      <!-- ── Today's Activity Tables ── -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

        <!-- Today Issued -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-arrow-circle-right" style="color:var(--primary);margin-right:8px;"></i>Issued Today</span>
            <span class="badge badge-primary"><?= count($todayIssued) ?></span>
          </div>
          <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
              <thead>
                <tr><th>Member</th><th>Book</th><th>Due</th></tr>
              </thead>
              <tbody>
                <?php if (empty($todayIssued)): ?>
                  <tr><td colspan="3" class="text-center text-muted" style="padding:20px;">Nothing issued yet today</td></tr>
                <?php else: foreach ($todayIssued as $t): ?>
                  <tr>
                    <td style="max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($t['member_name']) ?></td>
                    <td style="max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($t['book_title']) ?>"><?= e($t['book_title']) ?></td>
                    <td style="white-space:nowrap;"><?= formatDate($t['due_date'],'d M') ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Today Returned -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-arrow-circle-left" style="color:var(--success);margin-right:8px;"></i>Returned Today</span>
            <span class="badge badge-success"><?= count($todayReturned) ?></span>
          </div>
          <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
              <thead>
                <tr><th>Member</th><th>Book</th><th>Returned</th></tr>
              </thead>
              <tbody>
                <?php if (empty($todayReturned)): ?>
                  <tr><td colspan="3" class="text-center text-muted" style="padding:20px;">Nothing returned yet today</td></tr>
                <?php else: foreach ($todayReturned as $t): ?>
                  <tr>
                    <td style="max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($t['member_name']) ?></td>
                    <td style="max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($t['book_title']) ?>"><?= e($t['book_title']) ?></td>
                    <td style="white-space:nowrap;"><?= formatDate($t['return_date'],'d M') ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ── Recently Added Books ── -->
      <div class="card">
        <div class="card-header">
          <span><i class="fas fa-book-medical" style="color:var(--success);margin-right:8px;"></i>Recently Added Books</span>
          <a href="<?= BASE_URL ?>/views/admin/books/create.php" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add Book</a>
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr><th>Cover</th><th>Title</th><th>Author</th><th>Category</th><th>Available</th><th>Added</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php if (empty($recentBooks)): ?>
                <tr><td colspan="7" class="text-center text-muted" style="padding:32px;">No books added yet</td></tr>
              <?php else: foreach ($recentBooks as $b): ?>
              <tr>
                <td>
                  <img src="<?= BASE_URL ?>/uploads/books/<?= e($b['cover_image']) ?>"
                       onerror="this.src='<?= BASE_URL ?>/assets/images/default_book.png'"
                       style="width:32px;height:42px;object-fit:cover;border-radius:4px;border:1px solid var(--border);">
                </td>
                <td><strong><?= e($b['title']) ?></strong></td>
                <td><?= e($b['author_name'] ?: '—') ?></td>
                <td><?= e($b['category_name'] ?: '—') ?></td>
                <td>
                  <span class="badge <?= $b['available_quantity']>0?'badge-success':'badge-danger' ?>">
                    <?= $b['available_quantity'] ?>
                  </span>
                </td>
                <td><small class="text-muted"><?= formatDate($b['created_at'],'d M Y') ?></small></td>
                <td>
                  <a href="<?= BASE_URL ?>/views/admin/books/edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /page-content -->
  </div><!-- /main-content -->
</div><!-- /wrapper -->
<?php include __DIR__ . '/../../includes/footer.php'; ?>
