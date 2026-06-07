<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['member']);

require_once __DIR__ . '/../../models/MemberModel.php';
require_once __DIR__ . '/../../models/ReservationModel.php';
require_once __DIR__ . '/../../models/BookModel.php';

$memberModel      = new MemberModel();
$reservationModel = new ReservationModel();
$bookModel        = new BookModel();
$db               = Database::getInstance();

$member = $memberModel->findByUserId($_SESSION['user_id']);
if (!$member) redirect(BASE_URL . '/unauthorized.php');
$_SESSION['member'] = $member;

// Cancel reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $resId = (int)$_POST['cancel_id'];
        // Verify it belongs to this member
        $chk = $db->prepare("SELECT id FROM reservations WHERE id=? AND member_id=? AND status IN('pending','approved')");
        $chk->execute([$resId, $member['id']]);
        if ($chk->fetchColumn()) {
            $reservationModel->updateStatus($resId, 'cancelled');
            setFlash('success', 'Reservation cancelled.');
        }
    }
    redirect(BASE_URL . '/views/member/reservations.php');
}

$status   = $_GET['status'] ?? '';
$page     = max(1, (int)($_GET['page'] ?? 1));
$per      = 10;
$offset   = ($page - 1) * $per;

$where  = ["r.member_id = {$member['id']}"];
if ($status) $where[] = "r.status = '" . $db->quote($status) . "'";
$whereStr = implode(' AND ', $where);

$stmt = $db->query(
    "SELECT r.*, b.title AS book_title, b.cover_image, b.available_quantity
     FROM reservations r JOIN books b ON r.book_id=b.id
     WHERE r.member_id={$member['id']}" . ($status ? " AND r.status='".addslashes($status)."'" : "") . "
     ORDER BY r.created_at DESC LIMIT $per OFFSET $offset"
);
$reservations = $stmt->fetchAll();

$totalStmt = $db->query("SELECT COUNT(*) FROM reservations WHERE member_id={$member['id']}" . ($status ? " AND status='".addslashes($status)."'" : ""));
$total      = (int)$totalStmt->fetchColumn();
$pagination = paginate($total, $per, $page);

// Counts per status
$statusCounts = [];
foreach (['pending','approved','collected','cancelled','rejected'] as $s) {
    $cs = $db->prepare("SELECT COUNT(*) FROM reservations WHERE member_id=? AND status=?");
    $cs->execute([$member['id'], $s]);
    $statusCounts[$s] = (int)$cs->fetchColumn();
}

$pageTitle = 'My Reservations';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_member.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-content">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>" data-auto-dismiss><?= e($flash['message']) ?></div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1 class="page-title">My Reservations</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/member/dashboard.php">Dashboard</a> / Reservations</p>
        </div>
        <a href="<?= BASE_URL ?>/views/member/search.php" class="btn btn-primary">
          <i class="fas fa-plus"></i> Reserve a Book
        </a>
      </div>

      <!-- Status summary chips -->
      <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
        <a href="?status=" class="btn btn-sm <?= $status===''?'btn-primary':'btn-secondary' ?>">All (<?= array_sum($statusCounts) ?>)</a>
        <a href="?status=pending" class="btn btn-sm <?= $status==='pending'?'btn-primary':'btn-secondary' ?>">Pending (<?= $statusCounts['pending'] ?>)</a>
        <a href="?status=approved" class="btn btn-sm <?= $status==='approved'?'btn-primary':'btn-secondary' ?>">Approved (<?= $statusCounts['approved'] ?>)</a>
        <a href="?status=collected" class="btn btn-sm <?= $status==='collected'?'btn-primary':'btn-secondary' ?>">Collected (<?= $statusCounts['collected'] ?>)</a>
        <a href="?status=cancelled" class="btn btn-sm <?= $status==='cancelled'?'btn-primary':'btn-secondary' ?>">Cancelled (<?= $statusCounts['cancelled'] ?>)</a>
        <a href="?status=rejected" class="btn btn-sm <?= $status==='rejected'?'btn-primary':'btn-secondary' ?>">Rejected (<?= $statusCounts['rejected'] ?>)</a>
      </div>

      <?php if (empty($reservations)): ?>
        <div class="card">
          <div class="card-body text-center" style="padding:60px 20px;">
            <i class="fas fa-bookmark fa-3x" style="color:var(--border);margin-bottom:16px;"></i>
            <h3>No Reservations</h3>
            <p class="text-muted">You haven't reserved any books yet.</p>
            <a href="<?= BASE_URL ?>/views/member/search.php" class="btn btn-primary mt-3">
              <i class="fas fa-search"></i> Browse Books
            </a>
          </div>
        </div>
      <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
        <?php foreach ($reservations as $res):
          $statusColors = ['pending'=>'#f59e0b','approved'=>'#4f46e5','collected'=>'#10b981','cancelled'=>'#94a3b8','rejected'=>'#ef4444'];
          $statusIcons  = ['pending'=>'fa-hourglass-half','approved'=>'fa-check-circle','collected'=>'fa-box','cancelled'=>'fa-times-circle','rejected'=>'fa-ban'];
          $sc = $statusColors[$res['status']] ?? '#94a3b8';
          $si = $statusIcons[$res['status']] ?? 'fa-question';
          $canCancel = in_array($res['status'], ['pending','approved']);
        ?>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden;border-top:3px solid <?= $sc ?>;">
          <div style="display:flex;gap:12px;padding:14px 16px;">
            <img src="<?= BASE_URL ?>/uploads/books/<?= e($res['cover_image']) ?>"
                 onerror="this.src='<?= BASE_URL ?>/assets/images/default_book.png'"
                 style="width:52px;height:70px;object-fit:cover;border-radius:6px;border:1px solid var(--border);flex-shrink:0;">
            <div style="flex:1;min-width:0;">
              <div style="font-weight:700;font-size:.9rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:4px;" title="<?= e($res['book_title']) ?>"><?= e($res['book_title']) ?></div>
              <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:6px;">
                Reserved: <?= formatDate($res['reserved_date']) ?><br>
                Expires: <?= formatDate($res['expiry_date']) ?>
              </div>
              <div style="display:flex;align-items:center;gap:6px;">
                <i class="fas <?= $si ?>" style="color:<?= $sc ?>;font-size:.85rem;"></i>
                <span style="font-size:.82rem;font-weight:600;color:<?= $sc ?>;"><?= ucfirst($res['status']) ?></span>
              </div>
            </div>
          </div>
          <?php if ($canCancel): ?>
          <div style="padding:10px 16px;border-top:1px solid var(--border);background:var(--bg);">
            <form method="POST">
              <?= csrfField() ?>
              <input type="hidden" name="cancel_id" value="<?= $res['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger w-100" data-confirm="Cancel this reservation?">
                <i class="fas fa-times"></i> Cancel Reservation
              </button>
            </form>
          </div>
          <?php elseif ($res['status'] === 'approved'): ?>
          <div class="card-footer">
            <small class="text-success"><i class="fas fa-info-circle"></i> Your book is ready for pickup! Collect it before <?= formatDate($res['expiry_date']) ?>.</small>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($pagination['total_pages'] > 1): ?>
      <div style="display:flex;justify-content:center;margin-top:24px;">
        <div class="pagination">
          <?php for ($p = max(1,$pagination['current_page']-2); $p <= min($pagination['total_pages'],$pagination['current_page']+2); $p++): ?>
            <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="page-link <?= $p===$pagination['current_page']?'active':'' ?>"><?= $p ?></a>
          <?php endfor; ?>
        </div>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
