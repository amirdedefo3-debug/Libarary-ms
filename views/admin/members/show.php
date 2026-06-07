<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
middleware(['super_admin','librarian','assistant']);
require_once __DIR__ . '/../../../models/MemberModel.php';

$db = Database::getInstance();
$id = (int)($_GET['id'] ?? 0);

// Get member with user data
$stmt = $db->prepare(
    "SELECT m.*, u.full_name, u.email, u.phone, u.photo, u.gender, u.department, u.address, u.status AS user_status
     FROM members m
     JOIN users u ON m.user_id=u.id
     WHERE m.id=?"
);
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    setFlash('error','Member not found.');
    redirect(BASE_URL.'/views/admin/members/index.php');
}

// Borrow stats
$stats = $db->prepare(
    "SELECT
       COUNT(*) AS total,
       SUM(CASE WHEN status='borrowed' THEN 1 ELSE 0 END) AS active,
       SUM(CASE WHEN status='returned' THEN 1 ELSE 0 END) AS returned_count,
       SUM(CASE WHEN (status='borrowed' AND due_date < CURDATE()) OR status='overdue' THEN 1 ELSE 0 END) AS overdue
     FROM borrow_transactions WHERE member_id=?"
);
$stats->execute([$id]);
$borrowStats = $stats->fetch();

// Recent borrows
$borrows = $db->prepare(
    "SELECT bt.issue_number, bt.issue_date, bt.due_date, bt.return_date, bt.status, b.title
     FROM borrow_transactions bt
     JOIN books b ON bt.book_id=b.id
     WHERE bt.member_id=?
     ORDER BY bt.created_at DESC LIMIT 10"
);
$borrows->execute([$id]);
$borrowList = $borrows->fetchAll();

// Fines
$fineStmt = $db->prepare(
    "SELECT f.amount, f.status, f.created_at, b.title
     FROM fines f
     JOIN borrow_transactions bt ON f.borrow_id=bt.id
     JOIN books b ON bt.book_id=b.id
     WHERE f.member_id=?
     ORDER BY f.created_at DESC LIMIT 10"
);
$fineStmt->execute([$id]);
$fineList = $fineStmt->fetchAll();

$pageTitle = 'Member — '.$member['full_name'];
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Member Profile</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/members/index.php">Members</a> / <?= e($member['full_name']) ?></p>
        </div>
        <div class="d-flex gap-2">
          <?php if (hasPermission('members.edit')): ?>
          <a href="<?= BASE_URL ?>/views/admin/members/edit.php?id=<?= $id ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
          <?php endif; ?>
          <a href="<?= BASE_URL ?>/views/admin/transactions/issue.php" class="btn btn-success"><i class="fas fa-book"></i> Issue Book</a>
          <a href="<?= BASE_URL ?>/views/admin/members/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:300px 1fr;gap:24px;align-items:start;">
        <!-- Member Card -->
        <div class="card">
          <div class="card-body" style="text-align:center;">
            <img src="<?= BASE_URL ?>/uploads/profiles/<?= e($member['photo'] ?? 'default.png') ?>"
                 onerror="this.src='<?= BASE_URL ?>/assets/images/default.png'"
                 style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid var(--primary);margin-bottom:12px;">
            <h3 style="font-size:1.1rem;margin-bottom:4px;"><?= e($member['full_name']) ?></h3>
            <p class="text-muted" style="font-size:.85rem;"><?= e($member['email']) ?></p>
            <div class="badge <?= $member['status']==='active'?'badge-success':($member['status']==='suspended'?'badge-danger':'badge-warning') ?>" style="margin:8px 0;padding:6px 14px;">
              <?= ucfirst($member['status']) ?>
            </div>
            <hr>
            <div style="text-align:left;font-size:.85rem;">
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span class="text-muted">Member ID</span>
                <strong><?= e($member['member_id']) ?></strong>
              </div>
              <?php if ($member['student_id']): ?>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span class="text-muted">Student ID</span>
                <strong><?= e($member['student_id']) ?></strong>
              </div>
              <?php endif; ?>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span class="text-muted">Phone</span>
                <strong><?= e($member['phone'] ?: '—') ?></strong>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span class="text-muted">Department</span>
                <strong><?= e($member['department'] ?: '—') ?></strong>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span class="text-muted">Joined</span>
                <strong><?= formatDate($member['membership_date']) ?></strong>
              </div>
              <div style="display:flex;justify-content:space-between;">
                <span class="text-muted">Expires</span>
                <?php $expired = strtotime($member['expiry_date']) < time(); ?>
                <strong class="<?= $expired ? 'text-danger' : '' ?>"><?= formatDate($member['expiry_date']) ?></strong>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats + Details -->
        <div>
          <!-- Stats Row -->
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
            <div class="stat-card" style="padding:16px;">
              <div class="stat-icon blue" style="width:36px;height:36px;font-size:.9rem;"><i class="fas fa-book-open"></i></div>
              <div class="stat-info">
                <div class="stat-label" style="font-size:.75rem;">Total Borrows</div>
                <div class="stat-value" style="font-size:1.5rem;"><?= $borrowStats['total'] ?></div>
              </div>
            </div>
            <div class="stat-card" style="padding:16px;">
              <div class="stat-icon orange" style="width:36px;height:36px;font-size:.9rem;"><i class="fas fa-hand-holding"></i></div>
              <div class="stat-info">
                <div class="stat-label" style="font-size:.75rem;">Active</div>
                <div class="stat-value" style="font-size:1.5rem;"><?= $borrowStats['active'] ?></div>
              </div>
            </div>
            <div class="stat-card" style="padding:16px;">
              <div class="stat-icon green" style="width:36px;height:36px;font-size:.9rem;"><i class="fas fa-check"></i></div>
              <div class="stat-info">
                <div class="stat-label" style="font-size:.75rem;">Returned</div>
                <div class="stat-value" style="font-size:1.5rem;"><?= $borrowStats['returned_count'] ?></div>
              </div>
            </div>
            <div class="stat-card" style="padding:16px;">
              <div class="stat-icon red" style="width:36px;height:36px;font-size:.9rem;"><i class="fas fa-exclamation"></i></div>
              <div class="stat-info">
                <div class="stat-label" style="font-size:.75rem;">Overdue</div>
                <div class="stat-value" style="font-size:1.5rem;"><?= $borrowStats['overdue'] ?></div>
              </div>
            </div>
          </div>

          <!-- Borrow History -->
          <div class="card">
            <div class="card-header"><i class="fas fa-history" style="color:var(--primary);margin-right:8px;"></i>Borrow History</div>
            <div class="table-wrapper" style="border:none;border-radius:0;max-height:300px;overflow-y:auto;">
              <table>
                <thead><tr><th>Issue #</th><th>Book</th><th>Issued</th><th>Due</th><th>Status</th></tr></thead>
                <tbody>
                  <?php foreach ($borrowList as $bw): ?>
                  <tr>
                    <td><small><?= e($bw['issue_number']) ?></small></td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($bw['title']) ?></td>
                    <td><?= formatDate($bw['issue_date']) ?></td>
                    <td><?= formatDate($bw['due_date']) ?></td>
                    <td>
                      <?php $sc=['borrowed'=>'badge-info','returned'=>'badge-success','overdue'=>'badge-danger','lost'=>'badge-warning']; ?>
                      <span class="badge <?= $sc[$bw['status']] ?? 'badge-secondary' ?>"><?= ucfirst($bw['status']) ?></span>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if (empty($borrowList)): ?>
                  <tr><td colspan="5" class="text-center text-muted" style="padding:20px;">No borrows yet</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <?php if (!empty($fineList)): ?>
      <!-- Fines -->
      <div class="card" style="margin-top:20px;">
        <div class="card-header"><i class="fas fa-money-bill" style="color:var(--danger);margin-right:8px;"></i>Fine History</div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead><tr><th>Book</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach ($fineList as $fine): ?>
              <tr>
                <td><?= e($fine['title']) ?></td>
                <td><?= currency((float)$fine['amount']) ?></td>
                <td>
                  <span class="badge <?= $fine['status']==='paid'?'badge-success':($fine['status']==='waived'?'badge-warning':'badge-danger') ?>">
                    <?= ucfirst($fine['status']) ?>
                  </span>
                </td>
                <td><?= formatDate($fine['created_at']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
