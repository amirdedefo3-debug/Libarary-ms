<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
requireLogin();
require_once __DIR__ . '/../../models/UserModel.php';

$userModel = new UserModel();
$user      = $userModel->findById($_SESSION['user_id']);
$tab       = $_GET['tab'] ?? 'profile';
$error     = $success = '';

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
            $success = 'Password changed successfully.';
        }
    } else {
        $data = [
            'full_name'  => trim($_POST['full_name'] ?? ''),
            'phone'      => trim($_POST['phone'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'address'    => trim($_POST['address'] ?? ''),
        ];
        if (!empty($_FILES['photo']['name'])) {
            $up = uploadFile($_FILES['photo'], PROFILE_PHOTOS, ALLOWED_IMAGE_TYPES);
            if ($up) $data['photo'] = $up;
        }
        $userModel->update($_SESSION['user_id'], $data);
        $_SESSION['user'] = $userModel->findById($_SESSION['user_id']);
        $user   = $_SESSION['user'];
        $success = 'Profile updated successfully.';
    }
}

$pageTitle = 'My Profile';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <h1 class="page-title">My Profile</h1>
      </div>

      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <!-- Tabs -->
      <div style="display:flex;gap:8px;margin-bottom:20px;">
        <a href="?tab=profile" class="btn btn-sm <?= $tab==='profile'?'btn-primary':'btn-secondary' ?>"><i class="fas fa-user"></i> Profile</a>
        <a href="?tab=password" class="btn btn-sm <?= $tab==='password'?'btn-primary':'btn-secondary' ?>"><i class="fas fa-lock"></i> Change Password</a>
      </div>

      <div style="max-width:700px;">
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
                <label>New Password</label>
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
          <div class="card-header">Profile Information</div>
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
              <?= csrfField() ?>
              <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:20px;">
                <img id="photoPreview"
                     src="<?= BASE_URL ?>/uploads/profiles/<?= e($user['photo'] ?? 'default.png') ?>"
                     onerror="this.src='<?= BASE_URL ?>/assets/images/default.png'"
                     style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid var(--border);">
                <div style="flex:1;">
                  <label class="btn btn-secondary btn-sm" style="cursor:pointer;">
                    <i class="fas fa-camera"></i> Change Photo
                    <input type="file" name="photo" accept="image/*" style="display:none;" data-preview="photoPreview">
                  </label>
                  <p class="form-text">Max 5MB. JPEG or PNG.</p>
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
                <label>Email</label>
                <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled style="opacity:.7;">
                <p class="form-text">Email cannot be changed here.</p>
              </div>
              <div class="form-group">
                <label>Department</label>
                <input type="text" name="department" class="form-control" value="<?= e($user['department'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="form-control"><?= e($user['address'] ?? '') ?></textarea>
              </div>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
            </form>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
