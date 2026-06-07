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

$user = $db->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$_SESSION['user_id']]);
$user = $user->fetch();

$siteName = getSetting('site_name', 'Library Management System');
$pageTitle = 'Membership Card';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<style>
@media print {
  .main-content > *:not(.page-content),
  .sidebar, .navbar, .page-header .btn,
  .no-print { display: none !important; }
  body { background: #fff; }
  .membership-card { box-shadow: none !important; }
}
.membership-card {
  width: 420px;
  background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 60%, #06b6d4 100%);
  border-radius: 16px;
  padding: 28px;
  color: #fff;
  box-shadow: 0 20px 60px rgba(79,70,229,.35);
  position: relative;
  overflow: hidden;
  font-family: 'Inter', system-ui, sans-serif;
}
.membership-card::before {
  content: '';
  position: absolute;
  top: -60px; right: -60px;
  width: 200px; height: 200px;
  border-radius: 50%;
  background: rgba(255,255,255,.08);
}
.membership-card::after {
  content: '';
  position: absolute;
  bottom: -40px; left: -40px;
  width: 160px; height: 160px;
  border-radius: 50%;
  background: rgba(255,255,255,.05);
}
</style>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_member.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Membership Card</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/member/dashboard.php">Dashboard</a> / Card</p>
        </div>
        <div class="d-flex gap-2 no-print">
          <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print Card</button>
          <a href="<?= BASE_URL ?>/views/member/dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
      </div>

      <div style="display:flex;justify-content:center;margin-top:20px;">
        <div>
          <!-- FRONT CARD -->
          <p class="text-muted text-center mb-3" style="font-size:.85rem;">FRONT</p>
          <div class="membership-card" style="margin-bottom:24px;">
            <!-- Header -->
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
              <div style="width:40px;height:40px;background:rgba(255,255,255,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-book-open" style="font-size:1.2rem;"></i>
              </div>
              <div>
                <div style="font-size:.95rem;font-weight:700;line-height:1.2;"><?= e($siteName) ?></div>
                <div style="font-size:.7rem;opacity:.7;">Library Membership Card</div>
              </div>
            </div>

            <!-- Member Photo + Info -->
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
              <img src="<?= BASE_URL ?>/uploads/profiles/<?= e($user['photo'] ?? 'default.png') ?>"
                   onerror="this.src='<?= BASE_URL ?>/assets/images/default.png'"
                   style="width:70px;height:70px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.3);flex-shrink:0;">
              <div>
                <div style="font-size:1.1rem;font-weight:700;"><?= e($member['full_name']) ?></div>
                <div style="font-size:.78rem;opacity:.8;margin-top:2px;"><?= e($member['department'] ?: 'General Member') ?></div>
                <?php if ($member['student_id']): ?>
                  <div style="font-size:.75rem;opacity:.7;">Student ID: <?= e($member['student_id']) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Member ID + Dates -->
            <div style="background:rgba(255,255,255,.1);border-radius:10px;padding:14px 16px;margin-bottom:16px;">
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <div>
                  <div style="font-size:.65rem;opacity:.65;text-transform:uppercase;letter-spacing:.06em;">Member ID</div>
                  <div style="font-size:.95rem;font-weight:700;letter-spacing:.05em;"><?= e($member['member_id']) ?></div>
                </div>
                <div style="text-align:right;">
                  <div style="font-size:.65rem;opacity:.65;text-transform:uppercase;letter-spacing:.06em;">Expires</div>
                  <div style="font-size:.95rem;font-weight:700;"><?= formatDate($member['expiry_date'],'M Y') ?></div>
                </div>
              </div>
              <div style="display:flex;justify-content:space-between;">
                <div>
                  <div style="font-size:.65rem;opacity:.65;">Issued</div>
                  <div style="font-size:.82rem;"><?= formatDate($member['membership_date']) ?></div>
                </div>
                <div style="text-align:right;">
                  <div style="font-size:.65rem;opacity:.65;">Borrow Limit</div>
                  <div style="font-size:.82rem;"><?= $member['max_borrow_limit'] ?> books</div>
                </div>
              </div>
            </div>

            <!-- Status -->
            <div style="display:flex;align-items:center;justify-content:space-between;">
              <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;">
                <span style="width:8px;height:8px;background:#10b981;border-radius:50%;display:inline-block;"></span>
                <span style="opacity:.8;text-transform:capitalize;"><?= ucfirst($member['status']) ?></span>
              </div>
              <div style="font-size:.65rem;opacity:.5;">
                <?= e(getSetting('site_email','')) ?>
              </div>
            </div>
          </div>

          <!-- BACK CARD -->
          <p class="text-muted text-center mb-3" style="font-size:.85rem;">BACK</p>
          <div class="membership-card" style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);">
            <div style="margin-bottom:20px;">
              <div style="font-size:.7rem;opacity:.5;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">Terms & Conditions</div>
              <ul style="font-size:.75rem;opacity:.7;line-height:1.8;padding-left:16px;">
                <li>This card is non-transferable</li>
                <li>Report loss immediately to library staff</li>
                <li>Return borrowed books by the due date</li>
                <li>Fines: <?= currency((float)getSetting('fine_per_day','5')) ?>/day overdue</li>
                <li>Max <?= getSetting('borrow_limit','5') ?> books at a time</li>
              </ul>
            </div>
            <div style="border-top:1px solid rgba(255,255,255,.1);padding-top:14px;">
              <div style="font-size:.72rem;opacity:.5;margin-bottom:6px;">Contact Library</div>
              <div style="font-size:.82rem;opacity:.8;">
                <?= e(getSetting('site_phone','')) ?><br>
                <?= e(getSetting('site_address','')) ?>
              </div>
            </div>
            <div style="position:absolute;bottom:20px;right:20px;opacity:.15;font-size:4rem;">
              <i class="fas fa-book-open"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
