<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../controllers/MemberController.php';
$ctrl = new MemberController();
$ctrl->edit();
$pageTitle = 'Edit Member';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Edit Member</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/members/index.php">Members</a> / Edit</p>
        </div>
        <a href="<?= BASE_URL ?>/views/admin/members/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <div style="max-width:700px;">
        <form method="POST" enctype="multipart/form-data">
          <?= csrfField() ?>
          <input type="hidden" name="id" value="<?= $member['id'] ?>">

          <div class="card mb-4">
            <div class="card-header"><i class="fas fa-user" style="color:var(--primary);margin-right:8px;"></i>Personal Information</div>
            <div class="card-body">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                  <label>Full Name <span style="color:var(--danger);">*</span></label>
                  <input type="text" name="full_name" value="<?= e($member['full_name']) ?>" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Email <span style="color:var(--danger);">*</span></label>
                  <input type="email" name="email" value="<?= e($member['email']) ?>" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Phone</label>
                  <input type="text" name="phone" value="<?= e($member['phone'] ?? '') ?>" class="form-control">
                </div>
                <div class="form-group">
                  <label>Gender</label>
                  <select name="gender" class="form-control">
                    <option value="">Select</option>
                    <option value="male" <?= ($member['gender'] ?? '') === 'male' ? 'selected':'' ?>>Male</option>
                    <option value="female" <?= ($member['gender'] ?? '') === 'female' ? 'selected':'' ?>>Female</option>
                    <option value="other" <?= ($member['gender'] ?? '') === 'other' ? 'selected':'' ?>>Other</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Department</label>
                  <input type="text" name="department" value="<?= e($member['department'] ?? '') ?>" class="form-control">
                </div>
                <div class="form-group">
                  <label>Student ID</label>
                  <input type="text" name="student_id" value="<?= e($member['student_id'] ?? '') ?>" class="form-control">
                </div>
              </div>
              <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="form-control" rows="2"><?= e($member['address'] ?? '') ?></textarea>
              </div>
              <div class="form-group">
                <label>Profile Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
              </div>
            </div>
          </div>

          <div class="card mb-4">
            <div class="card-header"><i class="fas fa-id-card" style="color:var(--success);margin-right:8px;"></i>Membership Details</div>
            <div class="card-body">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                  <label>Membership Date</label>
                  <input type="date" name="membership_date" value="<?= $member['membership_date'] ?>" class="form-control">
                </div>
                <div class="form-group">
                  <label>Expiry Date</label>
                  <input type="date" name="expiry_date" value="<?= $member['expiry_date'] ?>" class="form-control">
                </div>
                <div class="form-group">
                  <label>Max Borrow Limit</label>
                  <input type="number" name="max_borrow_limit" value="<?= $member['max_borrow_limit'] ?>" class="form-control" min="1" max="20">
                </div>
                <div class="form-group">
                  <label>Status</label>
                  <select name="status" class="form-control">
                    <option value="active" <?= $member['user_status'] === 'active' ? 'selected':'' ?>>Active</option>
                    <option value="suspended" <?= $member['user_status'] === 'suspended' ? 'selected':'' ?>>Suspended</option>
                    <option value="inactive" <?= $member['user_status'] === 'inactive' ? 'selected':'' ?>>Inactive</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            <a href="<?= BASE_URL ?>/views/admin/members/index.php" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
