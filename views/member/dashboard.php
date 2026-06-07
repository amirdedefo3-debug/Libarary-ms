<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['member']);

require_once __DIR__ . '/../../models/MemberModel.php';
require_once __DIR__ . '/../../models/FineModel.php';
require_once __DIR__ . '/../../models/ReservationModel.php';
require_once __DIR__ . '/../../models/BookModel.php';

$memberModel      = new MemberModel();
$fineModel        = new FineModel();
$reservationModel = new ReservationModel();
$bookModel        = new BookModel();
$db               = Database::getInstance();

$member = $memberModel->findByUserId($_SESSION['user_id']);
if (!$member) { redirect(BASE_URL . '/unauthorized.php'); }

// Store member in session for sidebar badges
$_SESSION['member'] = $member;
$memberId = $member['id'];

// ── Stats ──────────────────────────────────────────────────
$s1 = $db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE member_id=? AND status='borrowed'");
$s1->execute([$memberId]);
$activeBorrows = (int)$s1->fetchColumn();

$s2 = $db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE member_id=?");
$s2->execute([$memberId]);
$totalBorrows = (int)$s2->fetchColumn();

$s3 = $db->prepare("SELECT COUNT(*) FROM reservations WHERE member_id=? AND status IN('pending','approved')");
$s3->execute([$memberId]);
$activeReservations = (int)$s3->fetchColumn();

$s4 = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM fines WHERE member_id=? AND status='pending'");
$s4->execute([$memberId]);
$pendingFines = (float)$s4->fetchColumn();

$s5 = $db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE member_id=? AND status='borrowed' AND due_date < CURDATE()");
$s5->execute([$memberId]);
$overdueCount = (int)$s5->fetchColumn();

// ── Active borrows list ────────────────────────────────────
$activeBorrowList = $db->prepare(
    "SELECT bt.id, bt.issue_number, bt.issue_date, bt.due_date, bt.status,
            b.title AS book_title, b.cover_image, a.name AS author_name
     FROM borrow_transactions bt
     JOIN books b ON bt.book_id=b.id
     LEFT JOIN authors a ON b.author_id=a.id
     WHERE bt.member_id=? AND bt.status='borrowed'
     ORDER BY bt.due_date ASC"
);
$activeBorrowList->execute([$memberId]);
$activeBorrowRows = $activeBorrowList->fetchAll();

// ── Recent borrow history ──────────────────────────────────
$historyStmt = $db->prepare(
    "SELECT bt.issue_date, bt.due_date, bt.return_date, bt.status,
            b.title AS book_title, b.cover_image
     FROM borrow_transactions bt
     JOIN books b ON bt.book_id=b.id
     WHERE bt.member_id=?
     ORDER BY bt.created_at DESC LIMIT 5"
);
$historyStmt->execute([$memberId]);
$recentHistory = $historyStmt->fetchAll();

// ── Notifications (unread) ─────────────────────────────────
$notifStmt = $db->prepare(
    "SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5"
);
$notifStmt->execute([$_SESSION['user_id']]);
$notifications = $notifStmt->fetchAll();
$unreadCount = array_sum(array_column($notifications, 'is_read') === false ? [1] : array_map(fn($n) => $n['is_read'] == 0 ? 1 : 0, $notifications));

// ── Featured / new books ──────────────────────────────────
$featuredBooks = $db->query(
    "SELECT b.id, b.title, b.cover_image, b.available_quantity, a.name AS author_name, c.name AS category_name
     FROM books b
     LEFT JOIN authors a ON b.author_id=a.id
     LEFT JOIN categories c ON b.category_id=c.id
     ORDER BY b.created_at DESC LIMIT 6"
)->fetchAll();

// ── Handle reservation from this page ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_book_id'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $id = $reservationModel->create($memberId, (int)$_POST['reserve_book_id']);
        setFlash($id ? 'success' : 'error', $id ? 'Book reserved successfully!' : 'Already reserved or error occurred.');
    }
    redirect(BASE_URL . '/views/member/dashboard.php');
}

$pageTitle = 'My Dashboard';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_member.php'; ?>

  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="page-content">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>" data-auto-dismiss>
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>

      <!-- ── Header ── -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Welcome, <?= e($member['full_name']) ?>! 👋</h1>
          <p class="page-breadcrumb">
            Member ID: <strong><?= e($member['member_id']) ?></strong>
            &nbsp;·&nbsp; Membership expires: <strong><?= formatDate($member['expiry_date']) ?></strong>
            &nbsp;·&nbsp;
            <span class="badge <?= $member['status']==='active'?'badge-success':'badge-danger' ?>"><?= ucfirst($member['status']) ?></span>
          </p>
        </div>
        <a href="<?= BASE_URL ?>/views/member/search.php" class="btn btn-primary">
          <i class="fas fa-search"></i> Search Books
        </a>
      </div>

      <!-- ── Overdue Warning ── -->
      <?php if ($overdueCount > 0): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>You have <?= $overdueCount ?> overdue book<?= $overdueCount>1?'s':'' ?>!</strong>
        Please return them as soon as possible to avoid additional fines.
        <a href="<?= BASE_URL ?>/views/member/borrows.php" style="color:inherit;text-decoration:underline;margin-left:8px;">View Now →</a>
      </div>
      <?php endif; ?>

      <!-- ── Stat Cards ── -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-book-open"></i></div>
          <div class="stat-info">
            <div class="stat-label">Currently Borrowed</div>
            <div class="stat-value"><?= $activeBorrows ?></div>
            <div class="stat-change text-muted">of <?= $member['max_borrow_limit'] ?> allowed</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-history"></i></div>
          <div class="stat-info">
            <div class="stat-label">Total Borrowed</div>
            <div class="stat-value"><?= $totalBorrows ?></div>
            <div class="stat-change text-muted">All time</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon yellow"><i class="fas fa-bookmark"></i></div>
          <div class="stat-info">
            <div class="stat-label">Active Reservations</div>
            <div class="stat-value"><?= $activeReservations ?></div>
            <div class="stat-change text-muted">Pending / Approved</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon <?= $pendingFines > 0 ? 'red' : 'green' ?>"><i class="fas fa-coins"></i></div>
          <div class="stat-info">
            <div class="stat-label">Pending Fines</div>
            <div class="stat-value"><?= currency($pendingFines) ?></div>
            <div class="stat-change <?= $pendingFines > 0 ? 'down' : '' ?>"><?= $pendingFines > 0 ? 'Please pay' : 'All clear!' ?></div>
          </div>
        </div>
      </div>

      <!-- ── Active Borrows + Quick Search ── -->
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">

        <!-- Active Borrows -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-book-open" style="color:var(--primary);margin-right:8px;"></i>My Active Borrows</span>
            <a href="<?= BASE_URL ?>/views/member/borrows.php" class="btn btn-sm btn-secondary">Full History</a>
          </div>
          <?php if (empty($activeBorrowRows)): ?>
            <div class="card-body text-center" style="padding:40px;">
              <i class="fas fa-book fa-3x" style="color:var(--border);margin-bottom:12px;"></i>
              <p class="text-muted">No active borrows right now.</p>
              <a href="<?= BASE_URL ?>/views/member/search.php" class="btn btn-primary btn-sm mt-2">
                <i class="fas fa-search"></i> Find a Book
              </a>
            </div>
          <?php else: ?>
          <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
              <thead>
                <tr><th>Book</th><th>Issued</th><th>Due Date</th><th>Status</th></tr>
              </thead>
              <tbody>
                <?php foreach ($activeBorrowRows as $bw):
                  $isOv = strtotime($bw['due_date']) < time();
                  $daysLeft = (int)floor((strtotime($bw['due_date']) - time()) / 86400);
                ?>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                      <img src="<?= BASE_URL ?>/uploads/books/<?= e($bw['cover_image']) ?>"
                           onerror="this.src='<?= BASE_URL ?>/assets/images/default_book.png'"
                           style="width:28px;height:38px;object-fit:cover;border-radius:3px;border:1px solid var(--border);">
                      <div>
                        <div style="font-size:.85rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px;" title="<?= e($bw['book_title']) ?>"><?= e($bw['book_title']) ?></div>
                        <div style="font-size:.75rem;color:var(--text-muted);"><?= e($bw['author_name'] ?: 'Unknown') ?></div>
                      </div>
                    </div>
                  </td>
                  <td><?= formatDate($bw['issue_date'],'d M') ?></td>
                  <td>
                    <?php if ($isOv): ?>
                      <span class="text-danger fw-bold"><?= formatDate($bw['due_date'],'d M') ?></span><br>
                      <small class="text-danger"><?= abs($daysLeft) ?>d overdue</small>
                    <?php elseif ($daysLeft <= 2): ?>
                      <span class="text-warning fw-bold"><?= formatDate($bw['due_date'],'d M') ?></span><br>
                      <small class="text-warning">Due soon!</small>
                    <?php else: ?>
                      <span><?= formatDate($bw['due_date'],'d M') ?></span><br>
                      <small class="text-muted"><?= $daysLeft ?>d left</small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge <?= $isOv ? 'badge-danger' : 'badge-info' ?>">
                      <?= $isOv ? 'Overdue' : 'Borrowed' ?>
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>

        <!-- Notifications Panel -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-bell" style="color:var(--warning);margin-right:8px;"></i>Notifications</span>
            <a href="<?= BASE_URL ?>/views/member/notifications.php" class="btn btn-sm btn-secondary">All</a>
          </div>
          <?php if (empty($notifications)): ?>
            <div class="card-body text-center text-muted" style="padding:32px 16px;">
              <i class="fas fa-bell-slash fa-2x" style="color:var(--border);margin-bottom:8px;"></i>
              <p>No notifications</p>
            </div>
          <?php else:
            $iconMap  = ['due_reminder'=>'fa-clock','overdue'=>'fa-exclamation','reservation'=>'fa-bookmark','fine'=>'fa-money-bill','general'=>'fa-info-circle'];
            $colorMap = ['due_reminder'=>'yellow','overdue'=>'red','reservation'=>'blue','fine'=>'red','general'=>'purple'];
            foreach ($notifications as $n):
              $ic = $iconMap[$n['type']] ?? 'fa-info-circle';
              $cl = $colorMap[$n['type']] ?? 'purple';
          ?>
            <div class="notification-item <?= $n['is_read']?'':'unread' ?>">
              <div class="notification-icon stat-icon <?= $cl ?>" style="width:32px;height:32px;font-size:.8rem;">
                <i class="fas <?= $ic ?>"></i>
              </div>
              <div class="notification-text">
                <div class="notification-title" style="font-size:.82rem;"><?= e($n['title']) ?></div>
                <div class="notification-msg" style="font-size:.75rem;"><?= e(substr($n['message'],0,60)) ?>...</div>
                <div class="notification-time"><?= formatDate($n['created_at'],'d M H:i') ?></div>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- ── Newly Added Books ── -->
      <div class="card">
        <div class="card-header">
          <span><i class="fas fa-star" style="color:var(--warning);margin-right:8px;"></i>Newly Added Books</span>
          <a href="<?= BASE_URL ?>/views/member/search.php" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Browse All</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;padding:16px;">
          <?php foreach ($featuredBooks as $book): ?>
          <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;transition:box-shadow .2s;cursor:pointer;"
               onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.1)'"
               onmouseout="this.style.boxShadow='none'">
            <img src="<?= BASE_URL ?>/uploads/books/<?= e($book['cover_image']) ?>"
                 onerror="this.src='<?= BASE_URL ?>/assets/images/default_book.png'"
                 style="width:100%;height:150px;object-fit:cover;">
            <div style="padding:10px;">
              <div style="font-size:.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:2px;" title="<?= e($book['title']) ?>"><?= e($book['title']) ?></div>
              <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:8px;"><?= e($book['author_name'] ?: 'Unknown') ?></div>
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="badge <?= $book['available_quantity']>0?'badge-success':'badge-danger' ?>" style="font-size:.65rem;">
                  <?= $book['available_quantity']>0 ? 'Available' : 'Borrowed' ?>
                </span>
                <?php if ($book['available_quantity'] < 1): ?>
                <form method="POST">
                  <?= csrfField() ?>
                  <input type="hidden" name="reserve_book_id" value="<?= $book['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-warning" style="padding:3px 8px;font-size:.72rem;" title="Reserve">
                    <i class="fas fa-bookmark"></i>
                  </button>
                </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div><!-- /page-content -->
  </div><!-- /main-content -->
</div><!-- /wrapper -->
<?php include __DIR__ . '/../../includes/footer.php'; ?>
