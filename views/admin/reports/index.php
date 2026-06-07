<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
requirePermission('reports.view');

$db   = Database::getInstance();
$type = $_GET['report'] ?? 'overview';

// Date range
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

// Overview stats
$totalBorrows     = (int)$db->query("SELECT COUNT(*) FROM borrow_transactions")->fetchColumn();
$totalReturns     = (int)$db->query("SELECT COUNT(*) FROM borrow_transactions WHERE status='returned'")->fetchColumn();
$totalFineAmount  = (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM fines")->fetchColumn();
$totalFineCollect = (float)$db->query("SELECT COALESCE(SUM(amount_paid),0) FROM payments")->fetchColumn();
$totalBooks       = (int)$db->query("SELECT COUNT(*) FROM books")->fetchColumn();
$totalMembers     = (int)$db->query("SELECT COUNT(*) FROM members")->fetchColumn();

// Monthly borrows for chart
$monthlyBorrows = $db->query(
    "SELECT DATE_FORMAT(issue_date,'%Y-%m') AS month, COUNT(*) AS cnt
     FROM borrow_transactions GROUP BY month ORDER BY month DESC LIMIT 12"
)->fetchAll();

// Monthly fines collected
$monthlyFines = $db->query(
    "SELECT DATE_FORMAT(payment_date,'%Y-%m') AS month, SUM(amount_paid) AS total
     FROM payments GROUP BY month ORDER BY month DESC LIMIT 12"
)->fetchAll();

// Top borrowed books
$topBooks = $db->query(
    "SELECT b.title, a.name AS author_name, COUNT(bt.id) AS cnt
     FROM borrow_transactions bt JOIN books b ON bt.book_id=b.id
     LEFT JOIN authors a ON b.author_id=a.id
     GROUP BY bt.book_id ORDER BY cnt DESC LIMIT 10"
)->fetchAll();

// Active members
$activeMembers = $db->query(
    "SELECT u.full_name, m.member_id, COUNT(bt.id) AS borrows
     FROM borrow_transactions bt
     JOIN members m ON bt.member_id=m.id
     JOIN users u ON m.user_id=u.id
     GROUP BY bt.member_id ORDER BY borrows DESC LIMIT 10"
)->fetchAll();

$pageTitle = 'Reports';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Reports & Analytics</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/dashboard.php">Dashboard</a> / Reports</p>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= BASE_URL ?>/api/export.php?type=books&format=csv" class="btn btn-secondary"><i class="fas fa-download"></i> Books CSV</a>
          <a href="<?= BASE_URL ?>/api/export.php?type=members&format=csv" class="btn btn-secondary"><i class="fas fa-download"></i> Members CSV</a>
          <a href="<?= BASE_URL ?>/api/export.php?type=transactions&format=csv" class="btn btn-secondary"><i class="fas fa-download"></i> Transactions CSV</a>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="stats-grid mb-4">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-book"></i></div>
          <div class="stat-info"><div class="stat-label">Total Books</div><div class="stat-value"><?= number_format($totalBooks) ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-users"></i></div>
          <div class="stat-info"><div class="stat-label">Total Members</div><div class="stat-value"><?= number_format($totalMembers) ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-exchange-alt"></i></div>
          <div class="stat-info"><div class="stat-label">Total Borrows</div><div class="stat-value"><?= number_format($totalBorrows) ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon cyan"><i class="fas fa-undo"></i></div>
          <div class="stat-info"><div class="stat-label">Total Returns</div><div class="stat-value"><?= number_format($totalReturns) ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow"><i class="fas fa-money-bill"></i></div>
          <div class="stat-info"><div class="stat-label">Total Fines</div><div class="stat-value"><?= currency($totalFineAmount) ?></div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div>
          <div class="stat-info"><div class="stat-label">Collected</div><div class="stat-value"><?= currency($totalFineCollect) ?></div></div>
        </div>
      </div>

      <!-- Charts -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <div class="card">
          <div class="card-header">Monthly Borrows</div>
          <div class="card-body"><canvas id="monthlyBorrowsChart" height="200"></canvas></div>
        </div>
        <div class="card">
          <div class="card-header">Monthly Fine Collection</div>
          <div class="card-body"><canvas id="monthlyFinesChart" height="200"></canvas></div>
        </div>
      </div>

      <!-- Tables -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <!-- Top Books -->
        <div class="card">
          <div class="card-header">Most Borrowed Books</div>
          <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
              <thead><tr><th>#</th><th>Title</th><th>Author</th><th>Borrows</th></tr></thead>
              <tbody>
                <?php foreach ($topBooks as $i => $b): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= e($b['title']) ?></td>
                  <td><?= e($b['author_name'] ?: '—') ?></td>
                  <td><span class="badge badge-primary"><?= $b['cnt'] ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Most Active Members -->
        <div class="card">
          <div class="card-header">Most Active Members</div>
          <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
              <thead><tr><th>#</th><th>Name</th><th>ID</th><th>Borrows</th></tr></thead>
              <tbody>
                <?php foreach ($activeMembers as $i => $am): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= e($am['full_name']) ?></td>
                  <td><small><?= e($am['member_id']) ?></small></td>
                  <td><span class="badge badge-info"><?= $am['borrows'] ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const mbLabels = <?= json_encode(array_column(array_reverse($monthlyBorrows),'month')) ?>;
const mbData   = <?= json_encode(array_map('intval',array_column(array_reverse($monthlyBorrows),'cnt'))) ?>;
const mfLabels = <?= json_encode(array_column(array_reverse($monthlyFines),'month')) ?>;
const mfData   = <?= json_encode(array_map('floatval',array_column(array_reverse($monthlyFines),'total'))) ?>;

new Chart(document.getElementById('monthlyBorrowsChart'), {
  type:'line',
  data:{labels:mbLabels,datasets:[{label:'Borrows',data:mbData,fill:true,borderColor:'#4f46e5',backgroundColor:'rgba(79,70,229,.1)',tension:.4,borderWidth:2}]},
  options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true},x:{grid:{display:false}}}}
});
new Chart(document.getElementById('monthlyFinesChart'), {
  type:'bar',
  data:{labels:mfLabels,datasets:[{label:'Collected',data:mfData,backgroundColor:'rgba(16,185,129,.7)',borderRadius:4}]},
  options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true},x:{grid:{display:false}}}}
});
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
