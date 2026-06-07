<?php $pageTitle = 'Forgot Password'; ?>
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
    <div class="auth-logo">
      <h2>Forgot Password</h2>
      <p>Enter your email to receive a reset link</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
      <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
      <?= csrfField() ?>
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control" required placeholder="your@email.com">
      </div>
      <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
    </form>
    <p class="text-center mt-3" style="font-size:.85rem;">
      <a href="<?= BASE_URL ?>/login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
    </p>
  </div>
</div>
<script>const saved=localStorage.getItem('lms-theme');if(saved)document.documentElement.dataset.theme=saved;</script>
</body></html>
