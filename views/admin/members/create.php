<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../controllers/MemberController.php';
$ctrl = new MemberController();
$ctrl->create();
$pageTitle = 'Add Member';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Add New Member</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/members/index.php">Members</a> / Add</p>
        </div>
        <a href="<?= BASE_URL ?>/views/admin/members/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div style="display:grid;grid-template-columns:1fr 280px;gap:20px;">
          <div>
            <div class="card mb-4">
              <div class="card-header">Personal Information</div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required value="<?= e($_POST['full_name'] ?? '') ?>">
                  </div>
                  <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" class="form-control" required value="<?= e($_POST['username'] ?? '') ?>">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                  </div>
                  <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control">
                      <option value="">— Select —</option>
                      <option value="male" <?= ($_POST['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                      <option value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                      <option value="other">Other</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Password</label>
                    <input type="text" name="password" class="form-control" value="Library@123" placeholder="Default: Library@123">
                  </div>
                </div>
                <div class="form-group">
                  <label>Department</label>
                  <input type="text" name="department" class="form-control" value="<?= e($_POST['department'] ?? '') ?>">
                </div>
                <div class="form-group">
                  <label>Address</label>
                  <textarea name="address" class="form-control" style="min-height:70px;"><?= e($_POST['address'] ?? '') ?></textarea>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header">Membership Details</div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" class="form-control" value="<?= e($_POST['student_id'] ?? '') ?>">
                  </div>
                  <div class="form-group">
                    <label>Max Borrow Limit</label>
                    <input type="number" name="max_borrow_limit" class="form-control" value="<?= e($_POST['max_borrow_limit'] ?? '5') ?>" min="1" max="20">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Membership Start</label>
                    <input type="date" name="membership_date" class="form-control" value="<?= date('Y-m-d') ?>">
                  </div>
                  <div class="form-group">
                    <label>Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 year')) ?>">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <div class="card">
              <div class="card-header">Photo</div>
              <div class="card-body" style="text-align:center;">
                <img id="photoPreview" src="<?= BASE_URL ?>/assets/images/default.png"
                     style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:3px solid var(--border);margin-bottom:12px;">
                <input type="file" name="photo" accept="image/*" class="form-control" data-preview="photoPreview">
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-user-plus"></i> Register Member</button>
          <a href="<?= BASE_URL ?>/views/admin/members/index.php" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
