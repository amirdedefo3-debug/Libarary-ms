<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['librarian']);

require_once __DIR__ . '/../../models/BookModel.php';
require_once __DIR__ . '/../../models/MemberModel.php';
require_once __DIR__ . '/../../models/TransactionModel.php';
require_once __DIR__ . '/../../models/FineModel.php';
require_once __DIR__ . '/../../models/ReservationModel.php';

$bookModel        = new BookModel();
$memberModel      = new MemberModel();
$txModel          = new TransactionModel();
$fineModel        = new FineModel();
$reservationModel = new ReservationModel();

$db = Database::getInstance();

// Librarian-focused stats
$totalBooks       = $bookModel->count();
$availableBooks   = $bookModel->countAvailable();
$issuedBooks      = $txModel->countByStatus('borrowed');
$returnedToday    = $txModel->countTodayReturned();
$overdueBooks     = $txModel->countByStatus('overdue') + (int)$db->query("SELECT COUNT(*) FROM borrow_transactions WHERE status='borrowed' AND due_date < CURDATE()")->fetchColumn();
$reservedBooks    = $reservationModel->countByStatus('pending');
$totalMembers     = $memberModel->count();
$newMembersToday  = $memberModel->countNewToday();

// Today's activity
$todayIssued = $db->query(
    "SELECT bt.issue_number, b.title, u.full_name, bt.due_date
     FROM borrow_transactions bt
     JOIN books b ON bt.book_id=b.id
     JOIN members m ON bt.member_id=m.id
     JOIN users u ON m.user_id=u.id
     WHERE DATE(bt.created_at) = CURDATE()
     ORDER BY bt.created_at DESC LIMIT 10"
)->fetchAll();

$todayReturned = $db->query(
    "SELECT rt.return_date, b.title, u.full_name, rt.fine_amount
     FROM return_transactions rt
     JOIN borrow_transactions bt ON rt.borrow_id=bt.id
     JOIN books b ON bt.book_id=b.id
     JOIN members m ON bt.member_id=m.id
     JOIN users u ON m.user_id=u.id
     WHERE DATE(rt.return_date) = CURDATE()
     ORDER BY rt.created_at DESC LIMIT 10"
)->fetchAll();

// Overdue books requiring attention
$overdueList = $db->query(
    "SELECT bt.issue_number, b.title, u.full_name, bt.due_date, 
            DATEDIFF(CURDATE(), bt.due_date) AS days_overdue
     FROM borrow_transactions bt
     JOIN books b ON bt.book_id=b.id
     JOIN members m ON bt.member_id=m.id
     JOIN users u ON m.user_id=u.id
     WHERE (bt.status='borrowed' AND bt.due_date < CURDATE()) OR bt.status='overdue'
     ORDER BY days_overdue DESC LIMIT 10"
)->fetchAll();

// Pending reservations
$pendingReservations = $db->query(
    "SELECT r.reservation_number, b.title, u.full_name, r.reserved_date
     FROM reservations r
     JOIN books b ON r.book_id=b.id
     JOIN members m ON r.member_id=m.id
     JOIN users u ON m.user_id=u.id
     WHERE r.status='pending'
     ORDER BY r.reserved_date ASC LIMIT 8"
)->fetchAll();

// Low stock books
$lowStockBooks = $db->query(
    "SELECT title, available_quantity, quantity
     FROM books 
     WHERE available_quantity <= 1 AND status='available'
     ORDER BY available_quantity ASC LIMIT 8"
)->fetchAll();

$pageTitle = 'Librarian Dashboard';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_librarian.php'; ?>

  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="page-content">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>" data-auto-dismiss>
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>

      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Librarian Dashboard</h1>
          <p class="page-breadcrumb">Manage daily library operations and book transactions</p>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Issue Book
          </a>
          <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Return Book
          </a>
          <a href="<?= BASE_URL ?>/views/admin/books/create.php" class="btn btn-success">
            <i class="fas fa-book-medical"></i> Add Book
          </a>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-books"></i></div>
          <div class="stat-info">
            <div class="stat-label">Available Books</div>
            <div class="stat-value"><?= number_format($availableBooks) ?></div>
            <div class="stat-change"><i class="fas fa-book-open"></i> of <?= number_format($totalBooks) ?> total</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange"><i class="fas fa-hand-holding"></i></div>
          <div class="stat-info">
            <div class="stat-label">Currently Issued</div>
            <div class="stat-value"><?= number_format($issuedBooks) ?></div>
            <div class="stat-change"><i class="fas fa-users"></i> Active borrows</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
          <div class="stat-info">
            <div class="stat-label">Returned Today</div>
            <div class="stat-value"><?= number_format($returnedToday) ?></div>
            <div class="stat-change up"><i class="fas fa-calendar-day"></i> Today's returns</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
          <div class="stat-info">
            <div class="stat-label">Overdue Books</div>
            <div class="stat-value"><?= number_format($overdueBooks) ?></div>
            <div class="stat-change down"><i class="fas fa-calendar-times"></i> Need attention</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-bookmark"></i></div>
          <div class="stat-info">
            <div class="stat-label">Pending Reservations</div>
            <div class="stat-value"><?= number_format($reservedBooks) ?></div>
            <div class="stat-change"><i class="fas fa-hourglass-half"></i> Awaiting approval</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon cyan"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <div class="stat-label">Total Members</div>
            <div class="stat-value"><?= number_format($totalMembers) ?></div>
            <div class="stat-change up"><i class="fas fa-user-plus"></i> <?= $newMembersToday ?> new today</div>
          </div>
        </div>
      </div>

      <!-- Main Dashboard Content -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <!-- Today's Issues -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-arrow-right" style="color:var(--success);margin-right:8px;"></i>Today's Issues</span>
            <a href="<?= BASE_URL ?>/views/admin/transactions/index.php" class="btn btn-sm btn-secondary">View All</a>
          </div>
          <div class="table-wrapper" style="border:none;border-radius:0;max-height:300px;overflow-y:auto;">
            <table>
              <thead>
                <tr>
                  <th>Issue #</th>
                  <th>Member</th>
                  <th>Book</th>
                  <th>Due Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($todayIssued as $issue): ?>
                <tr>
                  <td><span class="badge badge-info"><?= e($issue['issue_number']) ?></span></td>
                  <td><?= e($issue['full_name']) ?></td>
                  <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($issue['title']) ?>"><?= e($issue['title']) ?></td>
                  <td><?= formatDate($issue['due_date']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($todayIssued)): ?>
                <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">No books issued today</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Today's Returns -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-arrow-left" style="color:var(--info);margin-right:8px;"></i>Today's Returns</span>
            <a href="<?= BASE_URL ?>/views/admin/transactions/index.php?status=returned" class="btn btn-sm btn-secondary">View All</a>
          </div>
          <div class="table-wrapper" style="border:none;border-radius:0;max-height:300px;overflow-y:auto;">
            <table>
              <thead>
                <tr>
                  <th>Member</th>
                  <th>Book</th>
                  <th>Fine</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($todayReturned as $return): ?>
                <tr>
                  <td><?= e($return['full_name']) ?></td>
                  <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($return['title']) ?>"><?= e($return['title']) ?></td>
                  <td>
                    <?php if ($return['fine_amount'] > 0): ?>
                      <span class="badge badge-warning"><?= currency($return['fine_amount']) ?></span>
                    <?php else: ?>
                      <span class="text-success">Free</span>
                    <?php endif; ?>
                  </td>
                  <td><?= formatDate($return['return_date']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($todayReturned)): ?>
                <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">No books returned today</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Bottom Row - Overdue & Reservations -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <!-- Overdue Books -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-exclamation-triangle" style="color:var(--danger);margin-right:8px;"></i>Overdue Books</span>
            <a href="<?= BASE_URL ?>/views/admin/transactions/overdue.php" class="btn btn-sm btn-danger">View All</a>
          </div>
          <div class="table-wrapper" style="border:none;border-radius:0;max-height:320px;overflow-y:auto;">
            <table>
              <thead>
                <tr>
                  <th>Issue #</th>
                  <th>Member</th>
                  <th>Book</th>
                  <th>Days</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($overdueList as $overdue): ?>
                <tr>
                  <td><span class="badge badge-danger"><?= e($overdue['issue_number']) ?></span></td>
                  <td><?= e($overdue['full_name']) ?></td>
                  <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($overdue['title']) ?>"><?= e($overdue['title']) ?></td>
                  <td><span class="badge badge-warning">+<?= $overdue['days_overdue'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($overdueList)): ?>
                <tr><td colspan="4" class="text-center text-success" style="padding:24px;">
                  <i class="fas fa-check-circle"></i> No overdue books!
                </td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pending Reservations -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-bookmark" style="color:var(--warning);margin-right:8px;"></i>Pending Reservations</span>
            <a href="<?= BASE_URL ?>/views/admin/reservations/index.php" class="btn btn-sm btn-warning">Manage</a>
          </div>
          <div class="table-wrapper" style="border:none;border-radius:0;max-height:320px;overflow-y:auto;">
            <table>
              <thead>
                <tr>
                  <th>Reservation #</th>
                  <th>Member</th>
                  <th>Book</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pendingReservations as $reservation): ?>
                <tr>
                  <td><span class="badge badge-warning"><?= e($reservation['reservation_number']) ?></span></td>
                  <td><?= e($reservation['full_name']) ?></td>
                  <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($reservation['title']) ?>"><?= e($reservation['title']) ?></td>
                  <td><?= formatDate($reservation['reserved_date']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($pendingReservations)): ?>
                <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">No pending reservations</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <?php if (!empty($lowStockBooks)): ?>
      <!-- Low Stock Alert -->
      <div class="card" style="margin-top:20px;">
        <div class="card-header">
          <span><i class="fas fa-exclamation-circle" style="color:var(--warning);margin-right:8px;"></i>Low Stock Alert</span>
          <a href="<?= BASE_URL ?>/views/admin/books/index.php?filter=low_stock" class="btn btn-sm btn-warning">View All</a>
        </div>
        <div class="card-body">
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px;">
            <?php foreach ($lowStockBooks as $book): ?>
            <div class="alert alert-warning" style="margin:0;padding:12px;">
              <strong><?= e($book['title']) ?></strong><br>
              <small>Available: <?= $book['available_quantity'] ?> / Total: <?= $book['quantity'] ?></small>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div><!-- /page-content -->
  </div><!-- /main-content -->
</div><!-- /wrapper -->

<?php include __DIR__ . '/../../includes/footer.php'; ?>