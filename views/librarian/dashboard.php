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
$db               = Database::getInstance();

// ── Stats ──────────────────────────────────────────────────
$totalBooks      = $bookModel->count();
$availableBooks  = $bookModel->countAvailable();
$issuedBooks     = $txModel->countByStatus('borrowed');
$returnedToday   = $txModel->countTodayReturned();
$borrowedToday   = $txModel->countTodayIssued();
$overdueCount    = (int)$db->query("SELECT COUNT(*) FROM borrow_transactions WHERE status='borrowed' AND due_date < CURDATE()")->fetchColumn();
$reservedCount   = $reservationModel->countByStatus('pending');
$activeMembers   = $memberModel->countActive();
$newMembersMonth = (int)$db->query("SELECT COUNT(*) FROM members WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetchColumn();
$fineCollected   = $fineModel->totalCollected();
$finePending     = $fineModel->totalPending();

// ── Charts ─────────────────────────────────────────────────
// Weekly borrow/return (last 7 days)
$weeklyData = $db->query(
    "SELECT DATE(issue_date) AS day, COUNT(*) AS borrows
     FROM borrow_transactions
     WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY day ORDER BY day"
)->fetchAll();
$weeklyReturns = $db->query(
    "SELECT DATE(return_date) AS day, COUNT(*) AS returns
     FROM borrow_transactions
     WHERE return_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND return_date IS NOT NULL
     GROUP BY day ORDER BY day"
)->fetchAll();

// Build 7-day label array
$days = [];
$borrowsByDay  = [];
$returnsByDay  = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $days[] = date('D d', strtotime($d));
    $borrowsByDay[$d]  = 0;
    $returnsByDay[$d]  = 0;
}
foreach ($weeklyData    as $r) $borrowsByDay[$r['day']]  = (int)$r['borrows'];
foreach ($weeklyReturns as $r) $returnsByDay[$r['day']] = (int)$r['returns'];

// ── Recent transactions ────────────────────────────────────
$recentTx = $db->query(
    "SELECT bt.id, bt.issue_number, bt.issue_date, bt.due_date, bt.status,
            b.title AS book_title, b.cover_image,
            u.full_name AS member_name, m.member_id AS member_code
     FROM borrow_transactions bt
     JOIN books b    ON bt.book_id   = b.id
     JOIN members m  ON bt.member_id = m.id
     JOIN users u    ON m.user_id    = u.id
     ORDER BY bt.created_at DESC LIMIT 8"
)->fetchAll();

// ── Overdue list (top 5) ───────────────────────────────────
$overdueList = $db->query(
    "SELECT bt.issue_number, bt.due_date, b.title AS book_title, u.full_name AS member_name,
            DATEDIFF(CURDATE(), bt.due_date) AS days_over
     FROM borrow_transactions bt
     JOIN books b   ON bt.book_id   = b.id
     JOIN members m ON bt.member_id = m.id
     JOIN users u   ON m.user_id    = u.id
     WHERE bt.status='borrowed' AND bt.due_date < CURDATE()
     ORDER BY bt.due_date ASC LIMIT 5"
)->fetchAll();

// ── Pending reservations (top 5) ──────────────────────────
$pendingRes = $db->query(
    "SELECT r.id, r.reservation_number, r.reserved_date, b.title AS book_title,
            u.full_name AS member_name
     FROM reservations r
     JOIN books b   ON r.book_id    = b.id
     JOIN members m ON r.member_id  = m.id
     JOIN users u   ON m.user_id    = u.id
     WHERE r.status = 'pending'
     ORDER BY r.created_at ASC LIMIT 5"
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
        <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>" data-auto-dismiss>
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>

      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Librarian Dashboard</h1>
          <p class="page-breadcrumb">
            Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>,
            <strong><?= e(currentUser()['full_name']) ?></strong>!
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

      <!-- ── Stat Cards ── -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-book"></i></div>
          <div class="stat-info">
            <div class="stat-label">Available Books</div>
            <div class="stat-value"><?= number_format($availableBooks) ?></div>
            <div class="stat-change text-muted"><i class="fas fa-layer-group"></i> <?= number_format($totalBooks) ?> total</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-hand-holding-heart"></i></div>
          <div class="stat-info">
            <div class="stat-label">Currently Issued</div>
            <div class="stat-value"><?= number_format($issuedBooks) ?></div>
            <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?= $borrowedToday ?> today</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-undo-alt"></i></div>
          <div class="stat-info">
            <div class="stat-label">Returned Today</div>
            <div class="stat-value"><?= number_format($returnedToday) ?></div>
            <div class="stat-change text-muted"><i class="fas fa-calendar-day"></i> Today only</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
          <div class="stat-info">
            <div class="stat-label">Overdue Books</div>
            <div class="stat-value"><?= number_format($overdueCount) ?></div>
            <div class="stat-change down"><i class="fas fa-clock"></i> Need follow-up</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon yellow"><i class="fas fa-bookmark"></i></div>
          <div class="stat-info">
            <div class="stat-label">Pending Reservations</div>
            <div class="stat-value"><?= number_format($reservedCount) ?></div>
            <div class="stat-change text-muted"><i class="fas fa-hourglass-half"></i> Awaiting approval</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon cyan"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <div class="stat-label">Active Members</div>
            <div class="stat-value"><?= number_format($activeMembers) ?></div>
            <div class="stat-change up"><i class="fas fa-user-plus"></i> +<?= $newMembersMonth ?> this month</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-coins"></i></div>
          <div class="stat-info">
            <div class="stat-label">Fine Collected</div>
            <div class="stat-value"><?= currency($fineCollected) ?></div>
            <div class="stat-change down"><i class="fas fa-exclamation-circle"></i> <?= currency($finePending) ?> pending</div>
          </div>
        </div>
      </div>

      <!-- ── Quick Actions Strip ── -->
      <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
          <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <span style="font-weight:600;color:var(--text-muted);font-size:.85rem;margin-right:4px;">QUICK ACTIONS</span>
            <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="btn btn-primary btn-sm">
              <i class="fas fa-arrow-circle-right"></i> Issue Book
            </a>
            <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="btn btn-success btn-sm">
              <i class="fas fa-arrow-circle-left"></i> Return Book
            </a>
            <a href="<?= BASE_URL ?>/views/admin/books/create.php" class="btn btn-secondary btn-sm">
              <i class="fas fa-plus"></i> Add Book
            </a>
            <a href="<?= BASE_URL ?>/views/admin/members/create.php" class="btn btn-secondary btn-sm">
              <i class="fas fa-user-plus"></i> Add Member
            </a>
            <a href="<?= BASE_URL ?>/views/admin/transactions/overdue.php" class="btn btn-warning btn-sm">
              <i class="fas fa-exclamation-triangle"></i> Overdue List
            </a>
            <a href="<?= BASE_URL ?>/views/admin/reservations/index.php" class="btn btn-secondary btn-sm">
              <i class="fas fa-bookmark"></i> Reservations
            </a>
            <a href="<?= BASE_URL ?>/views/admin/fines/index.php" class="btn btn-secondary btn-sm">
              <i class="fas fa-money-bill-wave"></i> Fines
            </a>
          </div>
        </div>
      </div>

      <!-- ── Weekly Chart + Overdue ── -->
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">

        <!-- Weekly Borrow vs Return Chart -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-chart-bar" style="color:var(--primary);margin-right:8px;"></i>Weekly Activity (Last 7 Days)</span>
          </div>
          <div class="card-body">
            <div style="height:240px;">
              <canvas id="weeklyChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Overdue Alert Panel -->
        <div class="card">
          <div class="card-header" style="color:var(--danger);">
            <span><i class="fas fa-exclamation-triangle"></i> Top Overdue</span>
            <a href="<?= BASE_URL ?>/views/admin/transactions/overdue.php" class="btn btn-sm btn-danger">View All</a>
          </div>
          <?php if (empty($overdueList)): ?>
            <div class="card-body text-center text-muted" style="padding:32px 16px;">
              <i class="fas fa-check-circle fa-2x" style="color:var(--success);margin-bottom:8px;"></i>
              <p>No overdue books!</p>
            </div>
          <?php else: ?>
            <div style="overflow:hidden;">
              <?php foreach ($overdueList as $ov): ?>
              <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--border);">
                <div style="width:36px;height:36px;border-radius:8px;background:#fee2e2;color:var(--danger);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;font-size:.8rem;">
                  <?= $ov['days_over'] ?>d
                </div>
                <div style="min-width:0;flex:1;">
                  <div style="font-size:.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($ov['book_title']) ?></div>
                  <div style="font-size:.75rem;color:var(--text-muted);"><?= e($ov['member_name']) ?></div>
                </div>
                <a href="<?= BASE_URL ?>/views/admin/transactions/return.php?issue_number=<?= urlencode($ov['issue_number']) ?>"
                   class="btn btn-sm btn-success" style="flex-shrink:0;padding:4px 8px;">
                  Return
                </a>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ── Recent Transactions + Pending Reservations ── -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <!-- Recent Transactions -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-exchange-alt" style="color:var(--info);margin-right:8px;"></i>Recent Transactions</span>
            <a href="<?= BASE_URL ?>/views/admin/transactions/index.php" class="btn btn-sm btn-secondary">View All</a>
          </div>
          <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
              <thead>
                <tr><th>Member</th><th>Book</th><th>Due</th><th>Status</th></tr>
              </thead>
              <tbody>
                <?php if (empty($recentTx)): ?>
                  <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">No transactions yet</td></tr>
                <?php else: foreach ($recentTx as $tx):
                  $isOv = $tx['status']==='borrowed' && strtotime($tx['due_date']) < time();
                  $sc   = ['borrowed'=>'badge-info','returned'=>'badge-success','overdue'=>'badge-danger','lost'=>'badge-warning'];
                  $bc   = $isOv ? 'badge-danger' : ($sc[$tx['status']] ?? 'badge-secondary');
                  $sl   = $isOv ? 'Overdue' : ucfirst($tx['status']);
                ?>
                  <tr>
                    <td style="max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($tx['member_name']) ?></td>
                    <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($tx['book_title']) ?>"><?= e($tx['book_title']) ?></td>
                    <td style="white-space:nowrap;" class="<?= $isOv?'text-danger fw-bold':'' ?>"><?= formatDate($tx['due_date'],'d M') ?></td>
                    <td><span class="badge <?= $bc ?>"><?= $sl ?></span></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pending Reservations -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-bookmark" style="color:var(--warning);margin-right:8px;"></i>Pending Reservations</span>
            <a href="<?= BASE_URL ?>/views/admin/reservations/index.php" class="btn btn-sm btn-secondary">Manage</a>
          </div>
          <?php if (empty($pendingRes)): ?>
            <div class="card-body text-center text-muted" style="padding:32px 16px;">
              <i class="fas fa-bookmark fa-2x" style="color:var(--border);margin-bottom:8px;"></i>
              <p>No pending reservations</p>
            </div>
          <?php else: ?>
            <div style="overflow:hidden;">
              <?php foreach ($pendingRes as $res): ?>
              <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border);">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <i class="fas fa-bookmark"></i>
                </div>
                <div style="min-width:0;flex:1;">
                  <div style="font-size:.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($res['book_title']) ?></div>
                  <div style="font-size:.75rem;color:var(--text-muted);"><?= e($res['member_name']) ?> · <?= formatDate($res['reserved_date'],'d M') ?></div>
                </div>
                <form method="POST" action="<?= BASE_URL ?>/views/admin/reservations/index.php" style="display:flex;gap:4px;">
                  <?= csrfField() ?>
                  <input type="hidden" name="id" value="<?= $res['id'] ?>">
                  <input type="hidden" name="action" value="approved">
                  <button class="btn btn-sm btn-success" style="padding:4px 10px;">
                    <i class="fas fa-check"></i>
                  </button>
                </form>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /page-content -->
  </div><!-- /main-content -->
</div><!-- /wrapper -->

<script>
const days     = <?= json_encode(array_values($days)) ?>;
const borrows  = <?= json_encode(array_values($borrowsByDay)) ?>;
const returns  = <?= json_encode(array_values($returnsByDay)) ?>;

new Chart(document.getElementById('weeklyChart'), {
  type: 'bar',
  data: {
    labels: days,
    datasets: [
      {
        label: 'Issued',
        data: borrows,
        backgroundColor: 'rgba(79,70,229,.75)',
        borderRadius: 5,
        borderSkipped: false,
      },
      {
        label: 'Returned',
        data: returns,
        backgroundColor: 'rgba(16,185,129,.75)',
        borderRadius: 5,
        borderSkipped: false,
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'top', labels: { padding: 16, font: { size: 12 } } }
    },
    scales: {
      y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { stepSize: 1 } },
      x: { grid: { display: false } }
    }
  }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
