<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['member']);

require_once __DIR__ . '/../../models/MemberModel.php';
require_once __DIR__ . '/../../models/UserModel.php';

$memberModel = new MemberModel();
$userModel   = new UserModel();
$db          = Database::getInstance();

$member = $memberModel->findByUserId($_SESSION['user_id']);
if (!$member) redirect(BASE_URL . '/unauthorized.php');
$_SESSION['member'] = $member;

$user  = $userModel->findById($_SESSION['user_id']);
$tab   = $_GET['tab'] ?? 'profile';
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    if ($tab === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $userModel->updatePassword($_SESSION['user_id'], $new);
            $success = 'Password changed successfully!';
        }
    } else {
        $data = [
            'full_name'  => trim($_POST['full_name'] ?? ''),
            'phone'      => trim($_POST['phone'] ?? ''),
            'address'    => trim($_POST['address'] ?? ''),
        ];
        if (!empty($_FILES['photo']['name'])) {
            $up = uploadFile($_FILES['photo'], PROFILE_PHOTOS, ALLOWED_IMAGE_TYPES);
            if ($up) $data['photo'] = $up;
            else $error = 'Invalid image file.';
        }
        if (!$error) {
            $userModel->update($_SESSION['user_id'], $data);
            $_SESSION['user'] = $userModel->findById($_SESSION['user_id']);
            $user    = $_SESSION['user'];
            $success = 'Profile updated!';
        }
    }
}

// Borrow stats for profile
$totalB = $db->prepare("SELECT COUNT(*) FROM borrow_transactions WHERE member_id=?");
$totalB->execute([$member['id']]); $totalBorrows = (int)$totalB->fetchColumn();

$pageTitle = 'My Profile';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_member.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">My Profile</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/member/dashboard.php">Dashboard</a> / Profile</p>
        </div>
        <a href="<?= BASE_URL ?>/views/member/membership_card.php" class="btn btn-secondary">
          <i class="fas fa-id-card"></i> View Card
        </a>
      </div>

      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <div style="display:grid;grid-template-columns:280px 1fr;gap:20px;">

        <!-- Profile Card -->
        <div>
          <div class="card text-center" style="padding:28px 20px;">
            <img src="<?= BASE_URL ?>/uploads/profiles/<?= e($user['photo'] ?? 'default.png') ?>"
                 onerror="this.src='<?= BASE_URL ?>/assets/images/default.png'"
                 style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:4px solid var(--primary-light);margin:0 auto 16px;">
            <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:4px;"><?= e($user['full_name']) ?></h3>
            <p class="text-muted" style="font-size:.85rem;margin-bottom:12px;"><?= e($user['email']) ?></p>
            <span class="badge badge-primary" style="font-size:.8rem;margin-bottom:16px;">Member</span>

            <div style="text-align:left;padding-top:16px;border-top:1px solid var(--border);">
              <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:.85rem;border-bottom:1px solid var(--border);">
                <span class="text-muted">Member ID</span>
                <strong><?= e($member['member_id']) ?></strong>
              </div>
              <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:.85rem;border-bottom:1px solid var(--border);">
                <span class="text-muted">Joined</span>
                <strong><?= formatDate($member['membership_date']) ?></strong>
              </div>
              <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:.85rem;border-bottom:1px solid var(--border);">
                <span class="text-muted">Expires</span>
                <strong class="<?= strtotime($member['expiry_date'])<time()?'text-danger':'' ?>"><?= formatDate($member['expiry_date']) ?></strong>
              </div>
              <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:.85rem;border-bottom:1px solid var(--border);">
                <span class="text-muted">Borrow Limit</span>
                <strong><?= $member['max_borrow_limit'] ?> books</strong>
              </div>
              <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:.85rem;">
                <span class="text-muted">Total Borrows</span>
                <strong><?= $totalBorrows ?></strong>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Forms -->
        <div>
          <!-- Tabs -->
          <div style="display:flex;gap:8px;margin-bottom:20px;">
            <a href="?tab=profile" class="btn btn-sm <?= $tab==='profile'?'btn-primary':'btn-secondary' ?>"><i class="fas fa-user"></i> Edit Profile</a>
            <a href="?tab=password" class="btn btn-sm <?= $tab==='password'?'btn-primary':'btn-secondary' ?>"><i class="fas fa-lock"></i> Change Password</a>
          </div>

          <?php if ($tab === 'password'): ?>
          <div class="card">
            <div class="card-header">Change Password</div>
            <div class="card-body">
              <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                  <label>Current Password</label>
                  <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>New Password <small class="text-muted">(min. 8 characters)</small></label>
                  <input type="password" name="new_password" class="form-control" minlength="8" required>
                </div>
                <div class="form-group">
                  <label>Confirm New Password</label>
                  <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Password</button>
              </form>
            </div>
          </div>
          <?php else: ?>
          <div class="card">
            <div class="card-header">Personal Information</div>
            <div class="card-body">
              <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;padding:16px;background:var(--bg);border-radius:10px;">
                  <img id="photoPreview"
                       src="<?= BASE_URL ?>/uploads/profiles/<?= e($user['photo'] ?? 'default.png') ?>"
                       onerror="this.src='<?= BASE_URL ?>/assets/images/default.png'"
                       style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid var(--border);">
                  <div>
                    <label class="btn btn-secondary btn-sm" style="cursor:pointer;">
                      <i class="fas fa-camera"></i> Change Photo
                      <input type="file" name="photo" accept="image/*" style="display:none;" data-preview="photoPreview">
                    </label>
                    <p class="form-text">JPEG or PNG, max 5MB</p>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>">
                  </div>
                  <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
                  </div>
                </div>
                <div class="form-group">
                  <label>Email <small class="text-muted">(read-only)</small></label>
                  <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled style="opacity:.7;">
                </div>
                <div class="form-group">
                  <label>Address</label>
                  <textarea name="address" class="form-control"><?= e($user['address'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
              </form>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
