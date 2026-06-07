<?php
/**
 * Members List View — included by MemberController::index()
 * Variables: $members (array), $pagination (array)
 */
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../config/config.php';
    require_once __DIR__ . '/../../../includes/middleware.php';
    require_once __DIR__ . '/../../../controllers/MemberController.php';
    (new MemberController())->index();
    exit;
}
$pageTitle = 'Members';
?>
<?php include BASE_PATH . '/includes/header.php'; ?>
<div class="wrapper">
  <?php include BASE_PATH . '/includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include BASE_PATH . '/includes/navbar.php'; ?>
    <div class="page-content">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>" data-auto-dismiss>
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1 class="page-title">Members</h1>
          <p class="page-breadcrumb">
            <a href="<?= BASE_URL ?>/views/admin/dashboard.php">Dashboard</a> / Members
          </p>
        </div>
        <?php if (hasPermission('members.add')): ?>
        <a href="<?= BASE_URL ?>/views/admin/members/create.php" class="btn btn-primary">
          <i class="fas fa-user-plus"></i> Add Member
        </a>
        <?php endif; ?>
      </div>

      <!-- Search -->
      <div class="card mb-4">
        <div class="card-body" style="padding:12px 20px;">
          <form method="GET" style="display:flex;gap:12px;">
            <div style="flex:1;position:relative;">
              <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
              <input type="text" name="search" value="<?= e($_GET['search'] ?? '') ?>"
                     class="form-control" style="padding-left:36px;"
                     placeholder="Search name, ID, email...">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            <a href="<?= BASE_URL ?>/views/admin/members/index.php" class="btn btn-secondary">
              <i class="fas fa-times"></i>
            </a>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span>Members <span class="badge badge-primary"><?= number_format($pagination['total']) ?></span></span>
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr>
                <th>#</th><th>Member</th><th>Member ID</th><th>Phone</th>
                <th>Department</th><th>Expiry</th><th>Status</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($members)): ?>
              <tr>
                <td colspan="8" class="text-center text-muted" style="padding:40px;">No members found.</td>
              </tr>
              <?php else: foreach ($members as $i => $m): ?>
              <tr>
                <td><?= $pagination['offset'] + $i + 1 ?></td>
                <td>
                  <div class="d-flex align-center gap-2">
                    <img src="<?= BASE_URL ?>/uploads/profiles/<?= e($m['photo'] ?? 'default.png') ?>"
                         onerror="this.src='<?= BASE_URL ?>/assets/images/default.png'"
                         class="avatar avatar-sm">
                    <div>
                      <strong><?= e($m['full_name']) ?></strong><br>
                      <small class="text-muted"><?= e($m['email']) ?></small>
                    </div>
                  </div>
                </td>
                <td><small><?= e($m['member_id']) ?></small></td>
                <td><?= e($m['phone'] ?: '—') ?></td>
                <td><?= e($m['department'] ?: '—') ?></td>
                <td>
                  <?php $exp = strtotime($m['expiry_date']) < time(); ?>
                  <span class="<?= $exp ? 'text-danger' : '' ?>"><?= formatDate($m['expiry_date']) ?></span>
                </td>
                <td>
                  <span class="badge <?= $m['status'] === 'active' ? 'badge-success' : ($m['status'] === 'suspended' ? 'badge-danger' : 'badge-warning') ?>">
                    <?= ucfirst($m['status']) ?>
                  </span>
                </td>
                <td>
                  <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/views/admin/members/show.php?id=<?= $m['id'] ?>"
                       class="btn btn-sm btn-secondary" title="View">
                      <i class="fas fa-eye"></i>
                    </a>
                    <?php if (hasPermission('members.edit')): ?>
                    <a href="<?= BASE_URL ?>/views/admin/members/edit.php?id=<?= $m['id'] ?>"
                       class="btn btn-sm btn-primary" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('members.delete')): ?>
                    <form method="POST" action="<?= BASE_URL ?>/views/admin/members/delete.php" style="display:inline;">
                      <?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= $m['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger"
                              data-confirm="Delete member <?= e(addslashes($m['full_name'])) ?>?">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
          <small class="text-muted">
            Showing <?= $pagination['offset']+1 ?>–<?= min($pagination['offset']+$pagination['per_page'],$pagination['total']) ?>
            of <?= number_format($pagination['total']) ?>
          </small>
          <div class="pagination">
            <?php for ($p = max(1,$pagination['current_page']-2); $p <= min($pagination['total_pages'],$pagination['current_page']+2); $p++): ?>
              <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>"
                 class="page-link <?= $p===$pagination['current_page']?'active':'' ?>">
                <?= $p ?>
              </a>
            <?php endfor; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
