<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['assistant']);

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

// Assistant-focused stats (limited scope)
$totalBooks       = $bookModel->count();
$availableBooks   = $bookModel->countAvailable();
$issuedBooks      = $txModel->countByStatus('borrowed');
$returnedToday    = $txModel->countTodayReturned();
$issuedToday      = $txModel->countTodayIssued();
$activeMembers    = $memberModel->countActive();

// My recent activities (issued by current user)
$currentUserId = currentUser()['id'];
$myIssues = $db->query(
    "SELECT bt.issue_number, b.title, u.full_name, bt.issue_date, bt.due_date
     FROM borrow_transactions bt
     JOIN books b ON bt.book_id=b.id
     JOIN members m ON bt.member_id=m.id
     JOIN users u ON m.user_id=u.id
     WHERE bt.issued_by = ? AND DATE(bt.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
     ORDER BY bt.created_at DESC LIMIT 10",
    [$currentUserId]
)->fetchAll();

// Recently added books (by me)
$myBooks = $db->query(
    "SELECT b.title, b.isbn, c.name as category, b.created_at
     FROM books b
     LEFT JOIN categories c ON b.category_id=c.id
     LEFT JOIN activity_logs al ON al.description LIKE CONCAT('%', b.title, '%') AND al.user_id=?
     WHERE DATE(b.created_at) >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
     ORDER BY b.created_at DESC LIMIT 8",
    [$currentUserId]
)->fetchAll();

// Books due soon (next 3 days)
$dueSoon = $db->query(
    "SELECT bt.issue_number, b.title, u.full_name, bt.due_date,
            DATEDIFF(bt.due_date, CURDATE()) AS days_left
     FROM borrow_transactions bt
     JOIN books b ON bt.book_id=b.id
     JOIN members m ON bt.member_id=m.id
     JOIN users u ON m.user_id=u.id
     WHERE bt.status='borrowed' AND bt.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
     ORDER BY bt.due_date ASC LIMIT 10"
)->fetchAll();

// Popular books this month
$popularBooks = $db->query(
    "SELECT b.title, COUNT(bt.id) as borrow_count, c.name as category
     FROM books b
     JOIN borrow_transactions bt ON b.id=bt.book_id
     LEFT JOIN categories c ON b.category_id=c.id
     WHERE bt.issue_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     GROUP BY b.id
     ORDER BY borrow_count DESC
     LIMIT 8"
)->fetchAll();

// Quick search suggestions
$searchSuggestions = $db->query(
    "SELECT title, author_id, category_id
     FROM books 
     WHERE available_quantity > 0 
     ORDER BY RAND() LIMIT 6"
)->fetchAll();

$pageTitle = 'Assistant Dashboard';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_assistant.php'; ?>

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
          <h1 class="page-title">Assistant Dashboard</h1>
          <p class="page-breadcrumb">Welcome, <?= e(currentUser()['full_name']) ?>! Ready to help our members?</p>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Issue Book
          </a>
          <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Return Book
          </a>
          <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="btn btn-info">
            <i class="fas fa-search"></i> Search Books
          </a>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-book-open"></i></div>
          <div class="stat-info">
            <div class="stat-label">Available Books</div>
            <div class="stat-value"><?= number_format($availableBooks) ?></div>
            <div class="stat-change"><i class="fas fa-books"></i> Ready to issue</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange"><i class="fas fa-hand-paper"></i></div>
          <div class="stat-info">
            <div class="stat-label">Books Issued</div>
            <div class="stat-value"><?= number_format($issuedBooks) ?></div>
            <div class="stat-change"><i class="fas fa-arrow-right"></i> Currently out</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-plus-circle"></i></div>
          <div class="stat-info">
            <div class="stat-label">Issued Today</div>
            <div class="stat-value"><?= number_format($issuedToday) ?></div>
            <div class="stat-change up"><i class="fas fa-calendar-day"></i> Today's activity</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon cyan"><i class="fas fa-check-circle"></i></div>
          <div class="stat-info">
            <div class="stat-label">Returned Today</div>
            <div class="stat-value"><?= number_format($returnedToday) ?></div>
            <div class="stat-change up"><i class="fas fa-undo"></i> Processed returns</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <div class="stat-label">Active Members</div>
            <div class="stat-value"><?= number_format($activeMembers) ?></div>
            <div class="stat-change"><i class="fas fa-user-check"></i> Library users</div>
          </div>
        </div>
      </div>

      <!-- Quick Actions Panel -->
      <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
          <span><i class="fas fa-bolt" style="color:var(--warning);margin-right:8px;"></i>Quick Actions</span>
        </div>
        <div class="card-body">
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;">
            <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="quick-action-card">
              <div class="quick-action-icon blue"><i class="fas fa-arrow-right"></i></div>
              <div>
                <div class="quick-action-title">Issue a Book</div>
                <div class="quick-action-desc">Check out books to members</div>
              </div>
            </a>
            <a href="<?= BASE_URL ?>/views/admin/transactions/return.php" class="quick-action-card">
              <div class="quick-action-icon green"><i class="fas fa-arrow-left"></i></div>
              <div>
                <div class="quick-action-title">Return a Book</div>
                <div class="quick-action-desc">Process book returns</div>
              </div>
            </a>
            <a href="<?= BASE_URL ?>/views/admin/books/create.php" class="quick-action-card">
              <div class="quick-action-icon purple"><i class="fas fa-plus"></i></div>
              <div>
                <div class="quick-action-title">Add New Book</div>
                <div class="quick-action-desc">Add books to collection</div>
              </div>
            </a>
            <a href="<?= BASE_URL ?>/views/admin/members/index.php" class="quick-action-card">
              <div class="quick-action-icon orange"><i class="fas fa-users"></i></div>
              <div>
                <div class="quick-action-title">Find Members</div>
                <div class="quick-action-desc">Search member records</div>
              </div>
            </a>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <!-- My Recent Issues -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-user-check" style="color:var(--primary);margin-right:8px;"></i>My Recent Issues</span>
            <span class="badge badge-info"><?= count($myIssues) ?></span>
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
                <?php foreach ($myIssues as $issue): ?>
                <tr>
                  <td><span class="badge badge-success"><?= e($issue['issue_number']) ?></span></td>
                  <td><?= e($issue['full_name']) ?></td>
                  <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($issue['title']) ?>"><?= e($issue['title']) ?></td>
                  <td><?= formatDate($issue['due_date']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($myIssues)): ?>
                <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">No recent issues</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Books Due Soon -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-clock" style="color:var(--warning);margin-right:8px;"></i>Due Soon (Next 3 Days)</span>
            <span class="badge badge-warning"><?= count($dueSoon) ?></span>
          </div>
          <div class="table-wrapper" style="border:none;border-radius:0;max-height:300px;overflow-y:auto;">
            <table>
              <thead>
                <tr>
                  <th>Member</th>
                  <th>Book</th>
                  <th>Due Date</th>
                  <th>Days Left</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($dueSoon as $due): ?>
                <tr>
                  <td><?= e($due['full_name']) ?></td>
                  <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($due['title']) ?>"><?= e($due['title']) ?></td>
                  <td><?= formatDate($due['due_date']) ?></td>
                  <td>
                    <span class="badge <?= $due['days_left'] <= 1 ? 'badge-danger' : 'badge-warning' ?>">
                      <?= $due['days_left'] ?> days
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($dueSoon)): ?>
                <tr><td colspan="4" class="text-center text-success" style="padding:24px;">
                  <i class="fas fa-check-circle"></i> All books have time!
                </td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Bottom Row -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <!-- Popular Books This Month -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-fire" style="color:var(--danger);margin-right:8px;"></i>Popular This Month</span>
          </div>
          <div class="card-body">
            <?php if (empty($popularBooks)): ?>
              <p class="text-muted text-center">No borrowing data yet</p>
            <?php else: foreach ($popularBooks as $i => $book): ?>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
              <div style="width:24px;height:24px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.8rem;flex-shrink:0;"><?= $i+1 ?></div>
              <div style="flex:1;min-width:0;">
                <div style="font-weight:600;font-size:.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($book['title']) ?>"><?= e($book['title']) ?></div>
                <div style="font-size:.75rem;color:var(--text-muted);"><?= $book['borrow_count'] ?> borrows • <?= e($book['category'] ?? 'Uncategorized') ?></div>
              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <!-- Recent Books Added -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-plus-circle" style="color:var(--success);margin-right:8px;"></i>Recently Added Books</span>
          </div>
          <div class="card-body">
            <?php if (empty($myBooks)): ?>
              <p class="text-muted text-center">No recent additions</p>
            <?php else: foreach ($myBooks as $book): ?>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
              <div style="width:32px;height:32px;border-radius:6px;background:var(--success-light);color:var(--success);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-book" style="font-size:.8rem;"></i>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-weight:600;font-size:.875rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= e($book['title']) ?>"><?= e($book['title']) ?></div>
                <div style="font-size:.75rem;color:var(--text-muted);">
                  <?= e($book['category'] ?? 'Uncategorized') ?> • <?= formatDate($book['created_at']) ?>
                </div>
              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>

    </div><!-- /page-content -->
  </div><!-- /main-content -->
</div><!-- /wrapper -->

<style>
.quick-action-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  border: 1px solid var(--border);
  border-radius: 8px;
  text-decoration: none;
  color: var(--text);
  transition: all 0.2s ease;
}

.quick-action-card:hover {
  border-color: var(--primary);
  box-shadow: 0 2px 8px rgba(79, 70, 229, 0.1);
  transform: translateY(-1px);
}

.quick-action-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.quick-action-icon.blue { background: var(--primary-light); color: var(--primary); }
.quick-action-icon.green { background: var(--success-light); color: var(--success); }
.quick-action-icon.purple { background: var(--purple-light); color: var(--purple); }
.quick-action-icon.orange { background: var(--warning-light); color: var(--warning); }

.quick-action-title {
  font-weight: 600;
  margin-bottom: 2px;
  font-size: .9rem;
}

.quick-action-desc {
  font-size: .8rem;
  color: var(--text-muted);
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>