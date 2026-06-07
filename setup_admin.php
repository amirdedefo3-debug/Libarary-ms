<?php
/**
 * Admin Password Reset Helper
 * ─────────────────────────────────────────────────────────────
 * Visit this page ONCE to fix the admin password, then delete it.
 * URL: http://localhost/Library%20ms/Libarary-ms/setup_admin.php
 * ─────────────────────────────────────────────────────────────
 */

// Minimal DB connection — no framework needed
$host   = 'localhost';
$dbname = 'library_ms';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die('<p style="color:red;font-family:sans-serif;">DB connection failed: ' . htmlspecialchars($e->getMessage()) . '<br>Make sure XAMPP MySQL is running and the database <b>library_ms</b> exists (import database/schema.sql first).</p>');
}

// New credentials — change these if you want
$newEmail    = 'admin@library.com';
$newPassword = 'Admin@1234';
$newHash     = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

// Reset the admin user
$stmt = $pdo->prepare("UPDATE users SET password = ?, failed_attempts = 0, locked_until = NULL, status = 'active' WHERE email = ?");
$stmt->execute([$newHash, $newEmail]);
$affected = $stmt->rowCount();

// If no row existed (fresh DB didn't have the user), insert it
if ($affected === 0) {
    $stmt = $pdo->prepare(
        "INSERT INTO users (role_id, username, email, password, full_name, status, email_verified)
         VALUES (1, 'superadmin', ?, ?, 'Super Admin', 'active', 1)
         ON DUPLICATE KEY UPDATE password = ?, failed_attempts = 0, locked_until = NULL, status = 'active'"
    );
    $stmt->execute([$newEmail, $newHash, $newHash]);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Setup</title>
  <style>
    body { font-family: system-ui, sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; background:#f1f5f9; }
    .box { background:#fff; border-radius:12px; padding:40px; max-width:460px; width:100%; box-shadow:0 4px 20px rgba(0,0,0,.08); text-align:center; }
    .icon { font-size:3rem; margin-bottom:12px; }
    h2 { margin:0 0 8px; color:#1e293b; }
    p { color:#64748b; margin:6px 0; }
    .creds { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin:20px 0; text-align:left; }
    .creds p { margin:4px 0; color:#334155; }
    .creds strong { color:#4f46e5; }
    a.btn { display:inline-block; margin-top:16px; padding:10px 24px; background:#4f46e5; color:#fff; border-radius:8px; text-decoration:none; font-weight:600; }
    a.btn:hover { background:#3730a3; }
    .warn { margin-top:16px; padding:10px 14px; background:#fef3c7; border-radius:8px; font-size:.85rem; color:#92400e; }
  </style>
</head>
<body>
<div class="box">
  <div class="icon">✅</div>
  <h2>Admin Password Fixed!</h2>
  <p>The Super Admin account has been updated.</p>

  <div class="creds">
    <p>📧 Email: <strong><?= htmlspecialchars($newEmail) ?></strong></p>
    <p>🔑 Password: <strong><?= htmlspecialchars($newPassword) ?></strong></p>
  </div>

  <a href="login.php" class="btn">Go to Login →</a>

  <div class="warn">
    ⚠️ <strong>Delete this file</strong> after logging in:<br>
    <code>Libarary-ms/setup_admin.php</code>
  </div>
</div>
</body>
</html>
