<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
requireLogin();

$db = Database::getInstance();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            $db->prepare("INSERT INTO publishers (name,contact,email,address) VALUES (?,?,?,?)")->execute([
                $name, trim($_POST['contact']??''), trim($_POST['email']??''), trim($_POST['address']??'')
            ]);
            $success = 'Publisher added.';
        } else { $error = 'Name required.'; }
    } elseif ($action === 'delete') {
        $db->prepare("DELETE FROM publishers WHERE id=?")->execute([(int)$_POST['id']]);
        $success = 'Publisher deleted.';
    }
}

$publishers = $db->query("SELECT * FROM publishers ORDER BY name")->fetchAll();
$pageTitle  = 'Publishers';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header"><h1 class="page-title">Publishers</h1></div>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;">
        <div class="card">
          <div class="card-header">Add Publisher</div>
          <div class="card-body">
            <form method="POST">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="add">
              <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required></div>
              <div class="form-group"><label>Contact</label><input type="text" name="contact" class="form-control"></div>
              <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control"></div>
              <div class="form-group"><label>Address</label><textarea name="address" class="form-control"></textarea></div>
              <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Add Publisher</button>
            </form>
          </div>
        </div>

        <div class="card">
          <div class="card-header">All Publishers <span class="badge badge-primary"><?= count($publishers) ?></span></div>
          <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
              <thead><tr><th>#</th><th>Name</th><th>Contact</th><th>Email</th><th>Actions</th></tr></thead>
              <tbody>
                <?php foreach ($publishers as $i => $p): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><strong><?= e($p['name']) ?></strong></td>
                  <td><?= e($p['contact'] ?: '—') ?></td>
                  <td><?= e($p['email'] ?: '—') ?></td>
                  <td>
                    <form method="POST" style="display:inline;">
                      <?= csrfField() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $p['id'] ?>">
                      <button class="btn btn-sm btn-danger" data-confirm="Delete publisher?"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
