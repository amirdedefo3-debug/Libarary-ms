<?php $pageTitle = 'Login — Library Management System'; ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <script>
    const BASE_URL = "<?= BASE_URL ?>";
    (function(){ const t=localStorage.getItem('lms-theme'); if(t) document.documentElement.dataset.theme=t; })();
  </script>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card fade-in">
    <div class="auth-logo">
      <div style="width:64px;height:64px;background:linear-gradient(135deg,#4f46e5,#06b6d4);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto;">
        <i class="fas fa-book-open" style="color:#fff;font-size:1.8rem;"></i>
      </div>
      <h2><?= e(getSetting('site_name','Library Management System')) ?></h2>
      <p>Sign in to your account</p>
    </div>

    <?php if (isset($_GET['timeout'])): ?>
      <div class="alert alert-warning"><i class="fas fa-clock"></i> Session expired. Please login again.</div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?= csrfField() ?>

      <div class="form-group">
        <label for="email"><i class="fas fa-envelope" style="margin-right:6px;color:var(--text-muted);"></i>Email Address</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= e($_POST['email'] ?? '') ?>"
               placeholder="admin@library.com" required autofocus>
      </div>

      <div class="form-group">
        <label for="password">
          <i class="fas fa-lock" style="margin-right:6px;color:var(--text-muted);"></i>Password
          <a href="<?= BASE_URL ?>/forgot-password.php" style="float:right;font-size:.8rem;font-weight:400;">Forgot password?</a>
        </label>
        <div style="position:relative;">
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="••••••••" required>
          <button type="button" id="togglePass" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <div class="form-group" style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" id="remember" name="remember" style="width:16px;height:16px;cursor:pointer;">
        <label for="remember" style="margin-bottom:0;font-weight:400;cursor:pointer;">Keep me logged in</label>
      </div>

      <button type="submit" class="btn btn-primary w-100 btn-lg" style="margin-top:8px;">
        <i class="fas fa-sign-in-alt"></i> Sign In
      </button>
    </form>

    <p class="text-center text-muted mt-4" style="font-size:.82rem;">
      © <?= date('Y') ?> <?= e(getSetting('site_name','Library MS')) ?>. All rights reserved.
    </p>
  </div>
</div>

<script>
document.getElementById('togglePass')?.addEventListener('click', function() {
  const pwd = document.getElementById('password');
  const icon = this.querySelector('i');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    icon.className = 'fas fa-eye-slash';
  } else {
    pwd.type = 'password';
    icon.className = 'fas fa-eye';
  }
});
// Theme
const saved = localStorage.getItem('lms-theme');
if (saved) document.documentElement.dataset.theme = saved;
</script>
</body>
</html>
