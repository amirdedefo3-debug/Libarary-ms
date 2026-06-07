<?php $pageTitle = 'Reset Password'; ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card fade-in">
    <div class="auth-logo"><h2>Reset Password</h2></div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($user): ?>
    <form method="POST">
      <?= csrfField() ?>
      <div class="form-group">
        <label>New Password</label>
        <input type="password" name="password" class="form-control" minlength="8" required placeholder="Min 8 characters">
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
      </div>
      <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
    <?php endif; ?>
    <p class="text-center mt-3" style="font-size:.85rem;">
      <a href="<?= BASE_URL ?>/login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
    </p>
  </div>
</div>
<script>const saved=localStorage.getItem('lms-theme');if(saved)document.documentElement.dataset.theme=saved;</script>
</body></html>
