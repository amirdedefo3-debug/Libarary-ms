<?php
/**
 * Demo Users Setup — All Roles
 * ─────────────────────────────────────────────────────────────
 * Visit once to create/fix all demo accounts for all 4 roles.
 * URL: http://localhost/Library%20ms/Libarary-ms/setup_users.php
 * ─────────────────────────────────────────────────────────────
 */

$host   = 'localhost';
$dbname = 'library_ms';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die('<p style="color:red;font-family:sans-serif;padding:20px;">DB Error: ' . htmlspecialchars($e->getMessage()) . '<br><br>Make sure XAMPP MySQL is running and you have imported <b>database/schema.sql</b>.</p>');
}

// ── Demo accounts ──────────────────────────────────────────
$accounts = [
    [
        'role_id'   => 1,
        'username'  => 'superadmin',
        'email'     => 'admin@library.com',
        'password'  => 'Admin@1234',
        'full_name' => 'Super Admin',
        'role_name' => 'Super Admin',
        'color'     => '#4f46e5',
        'icon'      => '👑',
    ],
    [
        'role_id'   => 2,
        'username'  => 'librarian1',
        'email'     => 'librarian@library.com',
        'password'  => 'Librarian@123',
        'full_name' => 'John Librarian',
        'role_name' => 'Librarian',
        'color'     => '#10b981',
        'icon'      => '📚',
    ],
    [
        'role_id'   => 3,
        'username'  => 'assistant1',
        'email'     => 'assistant@library.com',
        'password'  => 'Assistant@123',
        'full_name' => 'Jane Assistant',
        'role_name' => 'Assistant Librarian',
        'color'     => '#f59e0b',
        'icon'      => '📖',
    ],
    [
        'role_id'   => 4,
        'username'  => 'member1',
        'email'     => 'member@library.com',
        'password'  => 'Member@123',
        'full_name' => 'Alice Member',
        'role_name' => 'Member / Student',
        'color'     => '#06b6d4',
        'icon'      => '👤',
    ],
];

$results = [];

foreach ($accounts as $acc) {
    $hash = password_hash($acc['password'], PASSWORD_BCRYPT, ['cost' => 12]);

    // Upsert — insert or update if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$acc['email']]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        // Update password, reset lock, ensure active
        $pdo->prepare(
            "UPDATE users SET
                password        = ?,
                role_id         = ?,
                username        = ?,
                full_name       = ?,
                status          = 'active',
                failed_attempts = 0,
                locked_until    = NULL,
                email_verified  = 1
             WHERE email = ?"
        )->execute([$hash, $acc['role_id'], $acc['username'], $acc['full_name'], $acc['email']]);
        $results[] = ['acc' => $acc, 'action' => 'updated'];
    } else {
        // Insert fresh
        $pdo->prepare(
            "INSERT INTO users (role_id, username, email, password, full_name, status, email_verified)
             VALUES (?, ?, ?, ?, ?, 'active', 1)"
        )->execute([$acc['role_id'], $acc['username'], $acc['email'], $hash, $acc['full_name']]);
        $userId = (int)$pdo->lastInsertId();

        // If member role, also create a members table record
        if ($acc['role_id'] === 4) {
            $memberId = 'MEM' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $pdo->prepare(
                "INSERT INTO members (user_id, member_id, membership_date, expiry_date, max_borrow_limit, status)
                 VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 5, 'active')"
            )->execute([$userId, $memberId]);
        }
        $results[] = ['acc' => $acc, 'action' => 'created'];
    }

    // Also ensure member record exists for existing member users
    if ($acc['role_id'] === 4 && $existing) {
        $chk = $pdo->prepare("SELECT id FROM members WHERE user_id = ?");
        $chk->execute([$existing]);
        if (!$chk->fetchColumn()) {
            $memberId = 'MEM' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $pdo->prepare(
                "INSERT INTO members (user_id, member_id, membership_date, expiry_date, max_borrow_limit, status)
                 VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 5, 'active')"
            )->execute([$existing, $memberId]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Demo Users Setup — Library MS</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: system-ui, -apple-system, sans-serif;
      background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }
    .container {
      background: #fff;
      border-radius: 16px;
      padding: 40px;
      width: 100%;
      max-width: 640px;
      box-shadow: 0 20px 60px rgba(0,0,0,.25);
    }
    h1 { font-size: 1.5rem; color: #1e293b; margin-bottom: 4px; }
    .subtitle { color: #64748b; font-size: .9rem; margin-bottom: 28px; }
    .card {
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 18px 20px;
      margin-bottom: 14px;
      display: flex;
      align-items: flex-start;
      gap: 16px;
    }
    .icon-box {
      width: 48px; height: 48px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem;
      flex-shrink: 0;
    }
    .info { flex: 1; }
    .role-name { font-weight: 700; font-size: 1rem; color: #1e293b; margin-bottom: 6px; }
    .badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 999px;
      font-size: .7rem;
      font-weight: 600;
      margin-left: 8px;
      vertical-align: middle;
    }
    .badge-created { background: #dcfce7; color: #15803d; }
    .badge-updated { background: #dbeafe; color: #1d4ed8; }
    .cred-row { display: flex; gap: 6px; align-items: center; font-size: .85rem; color: #475569; margin-top: 3px; }
    .cred-label { color: #94a3b8; min-width: 70px; }
    .cred-val { font-weight: 600; color: #1e293b; font-family: monospace; background: #f8fafc; padding: 1px 8px; border-radius: 4px; border: 1px solid #e2e8f0; }
    .divider { border: none; border-top: 1px solid #e2e8f0; margin: 24px 0; }
    .actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .btn {
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      font-size: .9rem;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-primary { background: #4f46e5; color: #fff; }
    .btn-primary:hover { background: #3730a3; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
    .warn {
      margin-top: 20px;
      padding: 12px 16px;
      background: #fef3c7;
      border-radius: 8px;
      font-size: .82rem;
      color: #92400e;
      border: 1px solid #fde68a;
    }
    code { background: #fef9c3; padding: 1px 6px; border-radius: 4px; font-size: .85em; }
  </style>
</head>
<body>
<div class="container">
  <h1>✅ All Demo Accounts Ready</h1>
  <p class="subtitle">All 4 user roles have been created/updated in the database.</p>

  <?php foreach ($results as $r):
    $acc = $r['acc'];
    $bg  = $acc['color'] . '18'; // transparent bg
  ?>
  <div class="card" style="border-color: <?= $acc['color'] ?>40;">
    <div class="icon-box" style="background: <?= $bg ?>;">
      <?= $acc['icon'] ?>
    </div>
    <div class="info">
      <div class="role-name">
        <?= htmlspecialchars($acc['role_name']) ?>
        <span class="badge <?= $r['action'] === 'created' ? 'badge-created' : 'badge-updated' ?>">
          <?= $r['action'] ?>
        </span>
      </div>
      <div class="cred-row">
        <span class="cred-label">Email</span>
        <span class="cred-val"><?= htmlspecialchars($acc['email']) ?></span>
      </div>
      <div class="cred-row">
        <span class="cred-label">Password</span>
        <span class="cred-val"><?= htmlspecialchars($acc['password']) ?></span>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

  <hr class="divider">

  <div class="actions">
    <a href="login.php" class="btn btn-primary">🔐 Go to Login</a>
  </div>

  <div class="warn">
    ⚠️ <strong>Delete this file</strong> after setup — it's a security risk to leave it public.<br>
    File to delete: <code>setup_users.php</code>
  </div>
</div>
</body>
</html>
