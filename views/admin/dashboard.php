<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['super_admin', 'librarian', 'assistant']);

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

// Stats
$totalBooks       = $bookModel->count();
$availableBooks   = $bookModel->countAvailable();
$totalMembers     = $memberModel->count();
$activeMembers    = $memberModel->countActive();
$borrowedToday    = $txModel->countTodayIssued();
$returnedToday    = $txModel->countTodayReturned();
$overdueCount     = $txModel->countByStatus('overdue') + (int)$db->query("SELECT COUNT(*) FROM borrow_transactions WHERE status='borrowed' AND due_date < CURDATE()")->fetchColumn();
$reservedCount    = $reservationModel->countByStatus('pending');
$fineCollected    = $fineModel->totalCollected();
$finePending      = $fineModel->totalPending();
$totalLibrarians  = (int)$db->query("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id=r.id WHERE r.slug IN('librarian','assistant')")->fetchColumn();

// Monthly borrow stats for chart (last 6 months)
$monthlyStats = $bookModel->getMonthlyStats(6);
$chartLabels  = array_column(array_reverse($monthlyStats), 'month');
$chartData    = array_column(array_reverse($monthlyStats), 'borrows');

// Most borrowed books
$topBooks = $bookModel->getMostBorrowed(5);

// Recent activity
$recentBorrows = $db->query(
    "SELECT bt.issue_date, b.title, u.full_name, bt.status
     FROM borrow_transactions bt
     JOIN books b ON bt.book_id=b.id
     JOIN members m ON bt.member_id=m.id
     JOIN users u ON m.user_id=u.id
     ORDER BY bt.created_at DESC LIMIT 8"
)->fetchAll();

// Top categories by borrow
$topCategories = $db->query(
    "SELECT c.name, COUNT(bt.id) AS cnt
     FROM borrow_transactions bt
     JOIN books b ON bt.book_id=b.id
     JOIN categories c ON b.category_id=c.id
     GROUP BY c.id ORDER BY cnt DESC LIMIT 5"
)->fetchAll();

$pageTitle = 'Dashboard';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_admin.php'; ?>

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
          <h1 class="page-title">Dashboard</h1>
          <p class="page-breadcrumb">Welcome back, <?= e(currentUser()['full_name'] ?? '') ?>! Here's what's happening today.</p>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Issue Book
          </a>
          <a href="<?= BASE_URL ?>/views/admin/members/create.php" class="btn btn-secondary">
            <i class="fas fa-user-plus"></i> Add Member
          </a>
        </div>
      </div>

      <!-- Stats Cards Row 1 -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-book"></i></div>
          <div class="stat-info">
            <div class="stat-label">Total Books</div>
            <div class="stat-value"><?= number_format($totalBooks) ?></div>
            <div class="stat-change up"><i class="fas fa-check-circle"></i> <?= number_format($availableBooks) ?> available</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <div class="stat-label">Total Members</div>
            <div class="stat-value"><?= number_format($totalMembers) ?></div>
            <div class="stat-change up"><i class="fas fa-user-check"></i> <?= number_format($activeMembers) ?> active</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-arrow-right"></i></div>
          <div class="stat-info">
            <div class="stat-label">Issued Today</div>
            <div class="stat-value"><?= number_format($borrowedToday) ?></div>
            <div class="stat-change"><i class="fas fa-calendar-day"></i> Books checked out</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon cyan"><i class="fas fa-arrow-left"></i></div>
          <div class="stat-info">
            <div class="stat-label">Returned Today</div>
            <div class="stat-value"><?= number_format($returnedToday) ?></div>
            <div class="stat-change"><i class="fas fa-undo"></i> Books returned</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
          <div class="stat-info">
            <div class="stat-label">Overdue</div>
            <div class="stat-value"><?= number_format($overdueCount) ?></div>
            <div class="stat-change down"><i class="fas fa-calendar-times"></i> Past due date</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow"><i class="fas fa-bookmark"></i></div>
          <div class="stat-info">
            <div class="stat-label">Reservations</div>
            <div class="stat-value"><?= number_format($reservedCount) ?></div>
            <div class="stat-change"><i class="fas fa-hourglass-half"></i> Pending approval</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div>
          <div class="stat-info">
            <div class="stat-label">Fine Collected</div>
            <div class="stat-value"><?= currency($fineCollected) ?></div>
            <div class="stat-change down"><i class="fas fa-hourglass"></i> <?= currency($finePending) ?> pending</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-user-tie"></i></div>
          <div class="stat-info">
            <div class="stat-label">Staff</div>
            <div class="stat-value"><?= number_format($totalLibrarians) ?></div>
            <div class="stat-change"><i class="fas fa-id-badge"></i> Librarians &amp; Assistants</div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:24px;">
        <!-- Monthly Borrow Chart -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-chart-line" style="color:var(--primary);margin-right:8px;"></i>Monthly Borrow Statistics</span>
            <select id="chartPeriodSelect" class="form-control" style="width:auto;padding:4px 10px;">
              <option value="6">Last 6 months</option>
              <option value="12">Last 12 months</option>
            </select>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height:260px;">
              <canvas id="borrowChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Top Categories -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-tags" style="color:var(--warning);margin-right:8px;"></i>Top Categories</span>
          </div>
          <div class="card-body">
            <div class="chart-container" style="height:260px;">
              <canvas id="categoryChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Row -->
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
                <tr>
                  <th>Member</th>
                  <th>Book</th>
                  <th>Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentBorrows as $row): ?>
                <tr>
                  <td><?= e($row['full_name']) ?></td>
                  <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($row['title']) ?>"><?= e($row['title']) ?></td>
                  <td><?= formatDate($row['issue_date']) ?></td>
                  <td>
                    <?php
                      $sc = ['borrowed'=>'badge-info','returned'=>'badge-success','overdue'=>'badge-danger','lost'=>'badge-warning'];
                      $bc = $sc[$row['status']] ?? 'badge-secondary';
                    ?>
                    <span class="badge <?= $bc ?>"><?= ucfirst($row['status']) ?></span>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentBorrows)): ?>
                <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">No transactions yet</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Most Borrowed Books -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-fire" style="color:var(--danger);margin-right:8px;"></i>Most Borrowed Books</span>
            <a href="<?= BASE_URL ?>/views/admin/reports/index.php" class="btn btn-sm btn-secondary">Reports</a>
          </div>
          <div class="card-body">
            <?php if (empty($topBooks)): ?>
              <p class="text-muted text-center">No data yet</p>
            <?php else: foreach ($topBooks as $i => $book): ?>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
              <div style="width:28px;height:28px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0;"><?= $i+1 ?></div>
              <div style="flex:1;min-width:0;">
                <div style="font-weight:600;font-size:.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($book['title']) ?>"><?= e($book['title']) ?></div>
                <div style="font-size:.75rem;color:var(--text-muted);"><?= $book['borrow_count'] ?> borrows</div>
              </div>
              <div style="width:80px;height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
                <div style="height:100%;background:var(--primary);width:<?= min(100, ($book['borrow_count'] / max(1, $topBooks[0]['borrow_count'])) * 100) ?>%;border-radius:3px;"></div>
              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>

    </div><!-- /page-content -->
  </div><!-- /main-content -->
</div><!-- /wrapper -->

<script>
// Borrow chart
const borrowCtx = document.getElementById('borrowChart').getContext('2d');
const borrowChart = new Chart(borrowCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [{
      label: 'Books Borrowed',
      data: <?= json_encode(array_map('intval', $chartData)) ?>,
      backgroundColor: 'rgba(79,70,229,.7)',
      borderColor: 'rgba(79,70,229,1)',
      borderWidth: 1,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' } },
      x: { grid: { display: false } }
    }
  }
});

// Category doughnut chart
const catCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(catCtx, {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_column($topCategories, 'name')) ?>,
    datasets: [{
      data: <?= json_encode(array_map('intval', array_column($topCategories, 'cnt'))) ?>,
      backgroundColor: ['#4f46e5','#06b6d4','#10b981','#f59e0b','#ef4444'],
      borderWidth: 2,
      borderColor: '#fff'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } }
    },
    cutout: '65%'
  }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
