<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
middleware(['super_admin']);
require_once __DIR__ . '/../../../models/UserModel.php';

$userModel = new UserModel();
$db        = Database::getInstance();
$page      = max(1, (int)($_GET['page'] ?? 1));
$search    = trim($_GET['search'] ?? '');
$role      = $_GET['role'] ?? '';
$error     = '';

// Handle suspend/activate/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $uid    = (int)($_POST['uid'] ?? 0);
    if ($uid === $_SESSION['user_id']) {
        setFlash('error', 'Cannot modify your own account here.');
    } else {
        if ($action === 'suspend') {
            $userModel->update($uid, ['status' => 'suspended']);
            setFlash('success', 'User suspended.');
        } elseif ($action === 'activate') {
            $userModel->update($uid, ['status' => 'active']);
            setFlash('success', 'User activated.');
        } elseif ($action === 'delete') {
            $userModel->delete($uid);
            setFlash('success', 'User deleted.');
        }
    }
    header('Location: ' . BASE_URL . '/views/admin/users/index.php');
    exit;
}

$result = $userModel->getAll($page, 20, $search, $role);
$users  = $result['data'];
$pagination = paginate($result['total'], 20, $page);
$roles  = $db->query("SELECT * FROM roles ORDER BY id")->fetchAll();
$pageTitle = 'User Management';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>" data-auto-dismiss><?= e($flash['message']) ?></div>
      <?php endif; ?>

      <div class="page-header">
        <h1 class="page-title">User Management</h1>
        <a href="<?= BASE_URL ?>/views/admin/members/create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add User</a>
      </div>

      <!-- Filters -->
      <div class="card mb-4">
        <div class="card-body" style="padding:12px 20px;">
          <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;position:relative;">
              <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
              <input type="text" name="search" value="<?= e($search) ?>" class="form-control" style="padding-left:36px;" placeholder="Search name, email...">
            </div>
            <select name="role" class="form-control" style="min-width:160px;">
              <option value="">All Roles</option>
              <?php foreach ($roles as $r): ?>
                <option value="<?= $r['slug'] ?>" <?= $role===$r['slug']?'selected':'' ?>><?= e($r['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
            <a href="<?= BASE_URL ?>/views/admin/users/index.php" class="btn btn-secondary"><i class="fas fa-times"></i></a>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header">All Users <span class="badge badge-primary"><?= number_format($pagination['total']) ?></span></div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr><th>#</th><th>User</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $i => $u): ?>
              <tr>
                <td><?= $pagination['offset']+$i+1 ?></td>
                <td>
                  <div class="d-flex align-center gap-2">
                    <img src="<?= BASE_URL ?>/uploads/profiles/<?= e($u['photo']??'default.png') ?>"
                         onerror="this.src='<?= BASE_URL ?>/assets/images/default.png'"
                         class="avatar avatar-sm">
                    <div>
                      <strong><?= e($u['full_name']) ?></strong><br>
                      <small class="text-muted"><?= e($u['email']) ?></small>
                    </div>
                  </div>
                </td>
                <td><span class="badge badge-primary"><?= e($u['role_name']) ?></span></td>
                <td>
                  <span class="badge <?= $u['status']==='active'?'badge-success':($u['status']==='suspended'?'badge-danger':'badge-warning') ?>">
                    <?= ucfirst($u['status']) ?>
                  </span>
                </td>
                <td><small class="text-muted"><?= $u['last_login'] ? formatDate($u['last_login'], 'd M Y H:i') : 'Never' ?></small></td>
                <td>
                  <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                  <div class="d-flex gap-2">
                    <?php if ($u['status'] === 'active'): ?>
                    <form method="POST" style="display:inline;"><?= csrfField() ?>
                      <input type="hidden" name="action" value="suspend">
                      <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                      <button class="btn btn-sm btn-warning" title="Suspend"><i class="fas fa-ban"></i></button>
                    </form>
                    <?php else: ?>
                    <form method="POST" style="display:inline;"><?= csrfField() ?>
                      <input type="hidden" name="action" value="activate">
                      <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                      <button class="btn btn-sm btn-success" title="Activate"><i class="fas fa-check"></i></button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" style="display:inline;"><?= csrfField() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                      <button class="btn btn-sm btn-danger" data-confirm="Delete user <?= e(addslashes($u['full_name'])) ?>?"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                  <?php else: ?>
                    <small class="text-muted">Current user</small>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
          <small class="text-muted">Showing <?= $pagination['offset']+1 ?>–<?= min($pagination['offset']+20,$pagination['total']) ?> of <?= number_format($pagination['total']) ?></small>
          <div class="pagination">
            <?php for ($p = max(1,$pagination['current_page']-2); $p <= min($pagination['total_pages'],$pagination['current_page']+2); $p++): ?>
              <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="page-link <?= $p===$pagination['current_page']?'active':'' ?>"><?= $p ?></a>
            <?php endfor; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
