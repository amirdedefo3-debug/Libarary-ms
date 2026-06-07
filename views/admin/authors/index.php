<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
requireLogin();

$db = Database::getInstance();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name  = trim($_POST['name'] ?? '');
        $bio   = trim($_POST['biography'] ?? '');
        $nat   = trim($_POST['nationality'] ?? '');
        if ($name) {
            $db->prepare("INSERT INTO authors (name,biography,nationality) VALUES (?,?,?)")->execute([$name,$bio,$nat]);
            $success = 'Author added.';
        } else { $error = 'Name required.'; }
    } elseif ($action === 'delete') {
        $db->prepare("DELETE FROM authors WHERE id=?")->execute([(int)$_POST['id']]);
        $success = 'Author deleted.';
    }
}

$authors   = $db->query("SELECT * FROM authors ORDER BY name")->fetchAll();
$pageTitle = 'Authors';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header"><h1 class="page-title">Authors</h1></div>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;">
        <div class="card">
          <div class="card-header">Add Author</div>
          <div class="card-body">
            <form method="POST">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="add">
              <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required></div>
              <div class="form-group"><label>Nationality</label><input type="text" name="nationality" class="form-control"></div>
              <div class="form-group"><label>Biography</label><textarea name="biography" class="form-control"></textarea></div>
              <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Add Author</button>
            </form>
          </div>
        </div>

        <div class="card">
          <div class="card-header">All Authors <span class="badge badge-primary"><?= count($authors) ?></span></div>
          <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
              <thead><tr><th>#</th><th>Name</th><th>Nationality</th><th>Actions</th></tr></thead>
              <tbody>
                <?php foreach ($authors as $i => $a): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><strong><?= e($a['name']) ?></strong></td>
                  <td><?= e($a['nationality'] ?: '—') ?></td>
                  <td>
                    <form method="POST" style="display:inline;">
                      <?= csrfField() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $a['id'] ?>">
                      <button class="btn btn-sm btn-danger" data-confirm="Delete author <?= e(addslashes($a['name'])) ?>?"><i class="fas fa-trash"></i></button>
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
