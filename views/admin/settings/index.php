<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
requirePermission('settings.manage');

$db = Database::getInstance();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid token.';
    } else {
        $keys = ['site_name','site_email','site_phone','site_address','fine_per_day','max_fine',
                 'borrow_limit','borrow_days','reservation_days','opening_hours','session_timeout','max_failed_attempts'];
        $stmt = $db->prepare("UPDATE settings SET value=? WHERE `key`=?");
        foreach ($keys as $k) {
            if (isset($_POST[$k])) {
                $stmt->execute([trim($_POST[$k]), $k]);
            }
        }
        logActivity('update_settings', 'settings', 'System settings updated');
        $success = 'Settings saved successfully.';
    }
}

// Load all settings
$allSettings = [];
$rows = $db->query("SELECT `key`, value FROM settings")->fetchAll();
foreach ($rows as $r) $allSettings[$r['key']] = $r['value'];

$pageTitle = 'System Settings';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">System Settings</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/dashboard.php">Dashboard</a> / Settings</p>
        </div>
      </div>

      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <form method="POST">
        <?= csrfField() ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

          <!-- General -->
          <div class="card">
            <div class="card-header"><i class="fas fa-cog" style="color:var(--primary);margin-right:8px;"></i>General Settings</div>
            <div class="card-body">
              <div class="form-group">
                <label>Library Name</label>
                <input type="text" name="site_name" class="form-control" value="<?= e($allSettings['site_name'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="email" name="site_email" class="form-control" value="<?= e($allSettings['site_email'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Phone</label>
                <input type="text" name="site_phone" class="form-control" value="<?= e($allSettings['site_phone'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Address</label>
                <textarea name="site_address" class="form-control"><?= e($allSettings['site_address'] ?? '') ?></textarea>
              </div>
              <div class="form-group">
                <label>Opening Hours</label>
                <input type="text" name="opening_hours" class="form-control" value="<?= e($allSettings['opening_hours'] ?? '') ?>">
              </div>
            </div>
          </div>

          <!-- Borrowing Rules -->
          <div>
            <div class="card mb-4">
              <div class="card-header"><i class="fas fa-book" style="color:var(--success);margin-right:8px;"></i>Borrowing Rules</div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group">
                    <label>Max Books Per Member</label>
                    <input type="number" name="borrow_limit" class="form-control" value="<?= e($allSettings['borrow_limit'] ?? '5') ?>" min="1">
                  </div>
                  <div class="form-group">
                    <label>Borrow Period (days)</label>
                    <input type="number" name="borrow_days" class="form-control" value="<?= e($allSettings['borrow_days'] ?? '14') ?>" min="1">
                  </div>
                </div>
                <div class="form-group">
                  <label>Reservation Expiry (days)</label>
                  <input type="number" name="reservation_days" class="form-control" value="<?= e($allSettings['reservation_days'] ?? '3') ?>" min="1">
                </div>
              </div>
            </div>

            <div class="card mb-4">
              <div class="card-header"><i class="fas fa-money-bill" style="color:var(--warning);margin-right:8px;"></i>Fine Settings</div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group">
                    <label>Fine Per Day ($)</label>
                    <input type="number" name="fine_per_day" class="form-control" step="0.01" value="<?= e($allSettings['fine_per_day'] ?? '5') ?>">
                  </div>
                  <div class="form-group">
                    <label>Maximum Fine ($)</label>
                    <input type="number" name="max_fine" class="form-control" step="0.01" value="<?= e($allSettings['max_fine'] ?? '500') ?>">
                  </div>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header"><i class="fas fa-shield-alt" style="color:var(--danger);margin-right:8px;"></i>Security Settings</div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group">
                    <label>Session Timeout (min)</label>
                    <input type="number" name="session_timeout" class="form-control" value="<?= e($allSettings['session_timeout'] ?? '30') ?>" min="5">
                  </div>
                  <div class="form-group">
                    <label>Max Login Attempts</label>
                    <input type="number" name="max_failed_attempts" class="form-control" value="<?= e($allSettings['max_failed_attempts'] ?? '5') ?>" min="3">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Settings</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
