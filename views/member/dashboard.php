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

if (!$member) {
    redirect(BASE_URL . '/unauthorized.php');
}

$memberId = $member['id'];

// Stats
$activeBorrows = (int)$db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE member_id=? AND status='borrowed'")->execute([$memberId]) ? (function() use ($db,$memberId){$s=$db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE member_id=? AND status='borrowed'");$s->execute([$memberId]);return(int)$s->fetchColumn();})() : 0;
$borrowHistory = (int)(function() use ($db,$memberId){$s=$db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE member_id=?");$s->execute([$memberId]);return(int)$s->fetchColumn();})();
$myFines       = $fineModel->getMemberFines($memberId);
$pendingFines  = array_sum(array_column(array_filter($myFines, fn($f) => $f['status']==='pending'), 'amount'));
$myReservations = $reservationModel->getMemberReservations($memberId);

// Borrow history
$borrows = $db->prepare(
    "SELECT bt.*, b.title AS book_title, b.cover_image FROM borrow_transactions bt
     JOIN books b ON bt.book_id=b.id WHERE bt.member_id=? ORDER BY bt.created_at DESC LIMIT 8"
);
$borrows->execute([$memberId]);
$borrowsList = $borrows->fetchAll();

// Search
$searchResults = [];
$searchQuery   = trim($_GET['search'] ?? '');
if ($searchQuery) {
    $result = $bookModel->getAll(1, 12, ['search' => $searchQuery, 'available' => '']);
    $searchResults = $result['data'];
}

// Handle reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_book_id'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        require_once __DIR__ . '/../../models/ReservationModel.php';
        $res = new ReservationModel();
        $id = $res->create($memberId, (int)$_POST['reserve_book_id']);
        if ($id) {
            setFlash('success', 'Book reserved successfully!');
        } else {
            setFlash('error', 'Could not reserve — you may already have a pending reservation for this book.');
        }
    }
    redirect(BASE_URL . '/views/member/dashboard.php');
}

$pageTitle = 'My Dashboard';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <!-- Member sidebar -->
  <nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div style="width:36px;height:36px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-book-open" style="color:#fff;"></i>
      </div>
      <div><h1><?= e(getSetting('site_name','Library MS')) ?></h1><span>Member Portal</span></div>
    </div>
    <div class="sidebar-nav">
      <div class="nav-item"><a href="<?= BASE_URL ?>/views/member/dashboard.php" class="nav-link active"><i class="fas fa-home"></i> Dashboard</a></div>
      <div class="nav-item"><a href="<?= BASE_URL ?>/views/member/dashboard.php?search=" class="nav-link"><i class="fas fa-search"></i> Search Books</a></div>
      <div class="nav-item"><a href="<?= BASE_URL ?>/views/member/reservations.php" class="nav-link"><i class="fas fa-bookmark"></i> My Reservations</a></div>
      <div class="nav-item"><a href="<?= BASE_URL ?>/views/member/borrows.php" class="nav-link"><i class="fas fa-history"></i> Borrow History</a></div>
      <div class="nav-item"><a href="<?= BASE_URL ?>/views/member/fines.php" class="nav-link"><i class="fas fa-money-bill"></i> My Fines</a></div>
      <div class="nav-item"><a href="<?= BASE_URL ?>/views/admin/profile.php" class="nav-link"><i class="fas fa-user"></i> My Profile</a></div>
    </div>
    <div class="sidebar-footer">
      <a href="<?= BASE_URL ?>/logout.php" class="nav-link" style="color:#f87171;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </nav>

  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-content">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>" data-auto-dismiss><?= e($flash['message']) ?></div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1 class="page-title">Welcome, <?= e($member['full_name']) ?>!</h1>
          <p class="page-breadcrumb">Member ID: <?= e($member['member_id']) ?> &nbsp;|&nbsp; Expires: <?= formatDate($member['expiry_date']) ?></p>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-book-open"></i></div>
          <div class="stat-info"><div class="stat-label">Currently Borrowed</div><div class="stat-value"><?= $activeBorrows ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-history"></i></div>
          <div class="stat-info"><div class="stat-label">Total Borrow History</div><div class="stat-value"><?= $borrowHistory ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow"><i class="fas fa-bookmark"></i></div>
          <div class="stat-info"><div class="stat-label">Reservations</div><div class="stat-value"><?= count($myReservations) ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-money-bill"></i></div>
          <div class="stat-info"><div class="stat-label">Pending Fines</div><div class="stat-value"><?= currency($pendingFines) ?></div></div>
        </div>
      </div>

      <!-- Book Search -->
      <div class="card mb-4">
        <div class="card-header"><i class="fas fa-search" style="color:var(--primary);margin-right:8px;"></i>Search Books</div>
        <div class="card-body">
          <form method="GET" style="display:flex;gap:12px;">
            <div style="flex:1;position:relative;">
              <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
              <input type="text" name="search" value="<?= e($searchQuery) ?>" class="form-control" style="padding-left:36px;" placeholder="Search by title, author, ISBN, category...">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
          </form>
        </div>
      </div>

      <?php if ($searchQuery): ?>
      <div class="card mb-4">
        <div class="card-header">Search Results for "<?= e($searchQuery) ?>" (<?= count($searchResults) ?> found)</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;padding:16px;">
          <?php if (empty($searchResults)): ?>
            <p class="text-muted" style="padding:8px;grid-column:1/-1;">No books found.</p>
          <?php else: foreach ($searchResults as $book): ?>
            <div class="card" style="overflow:hidden;">
              <img src="<?= BASE_URL ?>/uploads/books/<?= e($book['cover_image']) ?>"
                   onerror="this.src='<?= BASE_URL ?>/assets/images/default_book.png'"
                   style="width:100%;height:160px;object-fit:cover;">
              <div style="padding:12px;">
                <strong style="font-size:.875rem;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($book['title']) ?>"><?= e($book['title']) ?></strong>
                <small class="text-muted"><?= e($book['author_name'] ?: 'Unknown') ?></small>
                <div style="margin-top:8px;display:flex;justify-content:space-between;align-items:center;">
                  <span class="badge <?= $book['available_quantity']>0?'badge-success':'badge-danger' ?>">
                    <?= $book['available_quantity']>0 ? 'Available' : 'Borrowed' ?>
                  </span>
                  <?php if ($book['available_quantity'] < 1): ?>
                  <form method="POST" style="display:inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="reserve_book_id" value="<?= $book['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-bookmark"></i></button>
                  </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Current Borrows -->
      <div class="card">
        <div class="card-header"><i class="fas fa-book-open" style="color:var(--primary);margin-right:8px;"></i>Currently Borrowed</div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr><th>Book</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php if (empty($borrowsList)): ?>
              <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">No borrow history yet.</td></tr>
              <?php else: foreach ($borrowsList as $bw): ?>
              <tr>
                <td><?= e($bw['book_title']) ?></td>
                <td><?= formatDate($bw['issue_date']) ?></td>
                <td>
                  <?php $ov = $bw['status']==='borrowed' && strtotime($bw['due_date']) < time(); ?>
                  <span class="<?= $ov ? 'text-danger fw-bold' : '' ?>"><?= formatDate($bw['due_date']) ?></span>
                </td>
                <td>
                  <?php $sc=['borrowed'=>'badge-info','returned'=>'badge-success','overdue'=>'badge-danger','lost'=>'badge-warning']; ?>
                  <span class="badge <?= ($ov?'badge-danger':($sc[$bw['status']]??'badge-secondary')) ?>"><?= $ov?'Overdue':ucfirst($bw['status']) ?></span>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
