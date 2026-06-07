<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Access Denied';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--bg);">
  <div style="text-align:center;max-width:400px;padding:40px;">
    <div style="font-size:5rem;color:var(--danger);margin-bottom:16px;">🔒</div>
    <h1 style="font-size:2rem;font-weight:700;margin-bottom:8px;">Access Denied</h1>
    <p class="text-muted" style="margin-bottom:24px;">You don't have permission to access this page.</p>
    <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Go Back</a>
    &nbsp;
    <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary"><i class="fas fa-home"></i> Login</a>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
