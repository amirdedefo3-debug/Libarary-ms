<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
requireLogin();

$db    = Database::getInstance();
$error = $success = '';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name) {
            $db->prepare("INSERT INTO categories (name,description) VALUES (?,?)")->execute([$name,$desc]);
            $success = 'Category added.';
        } else { $error = 'Name required.'; }
    } elseif ($action === 'edit') {
        $db->prepare("UPDATE categories SET name=?,description=?,status=? WHERE id=?")->execute([
            trim($_POST['name']), trim($_POST['description']), $_POST['status'], (int)$_POST['id']
        ]);
        $success = 'Category updated.';
    } elseif ($action === 'delete') {
        $db->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_POST['id']]);
        $success = 'Category deleted.';
    }
}

$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$pageTitle   = 'Categories';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <h1 class="page-title">Categories</h1>
      </div>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;">
        <!-- Add form -->
        <div class="card">
          <div class="card-header">Add Category</div>
          <div class="card-body">
            <form method="POST">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="add">
              <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control"></textarea>
              </div>
              <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Add</button>
            </form>
          </div>
        </div>

        <!-- List -->
        <div class="card">
          <div class="card-header">All Categories <span class="badge badge-primary"><?= count($categories) ?></span></div>
          <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
              <thead><tr><th>#</th><th>Name</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
              <tbody>
                <?php foreach ($categories as $i => $cat): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><strong><?= e($cat['name']) ?></strong></td>
                  <td class="text-muted"><?= e(substr($cat['description'] ?? '',0,60)) ?></td>
                  <td><span class="badge <?= $cat['status']==='active'?'badge-success':'badge-warning' ?>"><?= ucfirst($cat['status']) ?></span></td>
                  <td>
                    <form method="POST" style="display:inline;">
                      <?= csrfField() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete category <?= e(addslashes($cat['name'])) ?>?"><i class="fas fa-trash"></i></button>
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
