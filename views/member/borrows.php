<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['member']);

require_once __DIR__ . '/../../models/MemberModel.php';

$memberModel = new MemberModel();
$db          = Database::getInstance();

$member = $memberModel->findByUserId($_SESSION['user_id']);
if (!$member) redirect(BASE_URL . '/unauthorized.php');
$_SESSION['member'] = $member;

$page   = max(1, (int)($_GET['page'] ?? 1));
$status = $_GET['status'] ?? '';
$per    = 15;
$offset = ($page - 1) * $per;

$where  = ["bt.member_id = {$member['id']}"];
$params = [];
if ($status) {
    $where[] = "bt.status = :status";
    $params[':status'] = $status;
}

$whereStr = implode(' AND ', $where);
$stmt = $db->prepare(
    "SELECT bt.*, b.title AS book_title, b.cover_image, b.isbn,
            a.name AS author_name, bt.issue_number
     FROM borrow_transactions bt
     JOIN books b ON bt.book_id = b.id
     LEFT JOIN authors a ON b.author_id = a.id
     WHERE $whereStr
     ORDER BY bt.created_at DESC
     LIMIT :lim OFFSET :off"
);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $per, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$borrows = $stmt->fetchAll();

$cStmt = $db->prepare("SELECT COUNT(*) FROM borrow_transactions bt WHERE $whereStr");
foreach ($params as $k => $v) $cStmt->bindValue($k, $v);
$cStmt->execute();
$total      = (int)$cStmt->fetchColumn();
$pagination = paginate($total, $per, $page);

// Summary counts
$counts = [];
foreach (['borrowed','returned','overdue','lost'] as $s) {
    $cs = $db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE member_id=? AND status=?");
    $cs->execute([$member['id'], $s]);
    $counts[$s] = (int)$cs->fetchColumn();
}
// Also count currently overdue (borrowed + past due)
$ovStmt = $db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE member_id=? AND status='borrowed' AND due_date < CURDATE()");
$ovStmt->execute([$member['id']]);
$counts['overdue_actual'] = (int)$ovStmt->fetchColumn();

$pageTitle = 'My Borrows';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_member.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">My Borrow History</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/member/dashboard.php">Dashboard</a> / My Borrows</p>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(160px,1fr));margin-bottom:20px;">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-book-open"></i></div>
          <div class="stat-info"><div class="stat-label">Currently Borrowed</div><div class="stat-value"><?= $counts['borrowed'] ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
          <div class="stat-info"><div class="stat-label">Returned</div><div class="stat-value"><?= $counts['returned'] ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
          <div class="stat-info"><div class="stat-label">Overdue</div><div class="stat-value"><?= $counts['overdue_actual'] ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow"><i class="fas fa-book"></i></div>
          <div class="stat-info"><div class="stat-label">Total Borrowed</div><div class="stat-value"><?= array_sum(array_values($counts)) - $counts['overdue_actual'] ?></div></div>
        </div>
      </div>

      <!-- Status Tabs -->
      <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <?php foreach (['' => 'All', 'borrowed' => 'Active', 'returned' => 'Returned', 'lost' => 'Lost'] as $v => $l): ?>
          <a href="?status=<?= $v ?>" class="btn btn-sm <?= $status===$v?'btn-primary':'btn-secondary' ?>"><?= $l ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="card-header">
          Borrow Records <span class="badge badge-primary"><?= number_format($total) ?></span>
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr><th>Book</th><th>ISBN</th><th>Issue #</th><th>Issued</th><th>Due Date</th><th>Returned</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php if (empty($borrows)): ?>
                <tr><td colspan="7" class="text-center text-muted" style="padding:40px;">No records found.</td></tr>
              <?php else: foreach ($borrows as $bw):
                $isOv = $bw['status']==='borrowed' && strtotime($bw['due_date']) < time();
                $daysOver = $isOv ? (int)floor((time()-strtotime($bw['due_date']))/86400) : 0;
                $sc = ['borrowed'=>'badge-info','returned'=>'badge-success','overdue'=>'badge-danger','lost'=>'badge-warning'];
                $bc = $isOv ? 'badge-danger' : ($sc[$bw['status']] ?? 'badge-secondary');
                $sl = $isOv ? 'Overdue' : ucfirst($bw['status']);
              ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:10px;">
                    <img src="<?= BASE_URL ?>/uploads/books/<?= e($bw['cover_image']) ?>"
                         onerror="this.src='<?= BASE_URL ?>/assets/images/default_book.png'"
                         style="width:28px;height:38px;object-fit:cover;border-radius:3px;border:1px solid var(--border);flex-shrink:0;">
                    <div>
                      <div style="font-weight:600;font-size:.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px;" title="<?= e($bw['book_title']) ?>"><?= e($bw['book_title']) ?></div>
                      <div style="font-size:.75rem;color:var(--text-muted);"><?= e($bw['author_name'] ?: 'Unknown') ?></div>
                    </div>
                  </div>
                </td>
                <td><small class="text-muted"><?= e($bw['isbn'] ?: '—') ?></small></td>
                <td><small class="text-muted"><?= e($bw['issue_number']) ?></small></td>
                <td><?= formatDate($bw['issue_date']) ?></td>
                <td>
                  <span class="<?= $isOv ? 'text-danger fw-bold' : '' ?>"><?= formatDate($bw['due_date']) ?></span>
                  <?php if ($isOv): ?>
                    <br><small class="text-danger"><?= $daysOver ?>d late</small>
                  <?php endif; ?>
                </td>
                <td><?= $bw['return_date'] ? formatDate($bw['return_date']) : '<span class="text-muted">—</span>' ?></td>
                <td><span class="badge <?= $bc ?>"><?= $sl ?></span></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
          <small class="text-muted">Showing <?= $pagination['offset']+1 ?>–<?= min($pagination['offset']+$per,$total) ?> of <?= $total ?></small>
          <div class="pagination">
            <?php for ($p = max(1,$pagination['current_page']-2); $p <= min($pagination['total_pages'],$pagination['current_page']+2); $p++): ?>
              <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="page-link <?= $p===$pagination['current_page']?'active':'' ?>"><?= $p ?></a>
            <?php endfor; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
