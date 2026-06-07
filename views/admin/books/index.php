<?php
/**
 * Books List View — included by BookController::index()
 * Variables: $books (array), $pagination (array), $categories (array), $filters (array)
 */
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../config/config.php';
    require_once __DIR__ . '/../../../includes/middleware.php';
    require_once __DIR__ . '/../../../controllers/BookController.php';
    (new BookController())->index();
    exit;
}
$pageTitle = 'Books';
?>
<?php include BASE_PATH . '/includes/header.php'; ?>
<div class="wrapper">
  <?php include BASE_PATH . '/includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>" data-auto-dismiss><?= e($flash['message']) ?></div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1 class="page-title">Book Management</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/dashboard.php">Dashboard</a> / Books</p>
        </div>
        <div class="d-flex gap-2">
          <?php if (hasPermission('books.add')): ?>
          <a href="<?= BASE_URL ?>/views/admin/books/create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Book</a>
          <?php endif; ?>
          <a href="<?= BASE_URL ?>/api/export.php?type=books&format=csv" class="btn btn-secondary"><i class="fas fa-download"></i> Export CSV</a>
        </div>
      </div>

      <!-- Filters -->
      <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
          <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
              <label style="font-size:.8rem;">Search</label>
              <div style="position:relative;">
                <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                <input type="text" name="search" value="<?= e($filters['search']) ?>" class="form-control" style="padding-left:36px;" placeholder="Title, ISBN, Author...">
              </div>
            </div>
            <div style="min-width:160px;">
              <label style="font-size:.8rem;">Category</label>
              <select name="category_id" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="min-width:130px;">
              <label style="font-size:.8rem;">Availability</label>
              <select name="available" class="form-control">
                <option value="">All</option>
                <option value="1" <?= $filters['available']==='1'?'selected':'' ?>>Available</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
          </form>
        </div>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="card-header">
          <span>Books <span class="badge badge-primary"><?= number_format($pagination['total']) ?></span></span>
          <input type="text" id="tableSearch" placeholder="Quick filter..." class="form-control" style="width:200px;padding:6px 12px;">
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Cover</th>
                <th>Title</th>
                <th>ISBN</th>
                <th>Author</th>
                <th>Category</th>
                <th>Qty</th>
                <th>Available</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($books)): ?>
              <tr><td colspan="10" class="text-center text-muted" style="padding:40px;">No books found.</td></tr>
              <?php else: foreach ($books as $i => $book): ?>
              <tr>
                <td><?= ($pagination['offset'] + $i + 1) ?></td>
                <td>
                  <img src="<?= BASE_URL ?>/uploads/books/<?= e($book['cover_image']) ?>"
                       onerror="this.src='<?= BASE_URL ?>/assets/images/default_book.png'"
                       style="width:36px;height:48px;object-fit:cover;border-radius:4px;border:1px solid var(--border);">
                </td>
                <td>
                  <strong><?= e($book['title']) ?></strong>
                  <?php if ($book['subtitle']): ?><br><small class="text-muted"><?= e($book['subtitle']) ?></small><?php endif; ?>
                </td>
                <td><small><?= e($book['isbn'] ?: '—') ?></small></td>
                <td><?= e($book['author_name'] ?: '—') ?></td>
                <td><?= e($book['category_name'] ?: '—') ?></td>
                <td><?= $book['quantity'] ?></td>
                <td>
                  <span class="badge <?= $book['available_quantity'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                    <?= $book['available_quantity'] ?>
                  </span>
                </td>
                <td>
                  <span class="badge <?= $book['available_quantity'] > 0 ? 'badge-success' : 'badge-warning' ?>">
                    <?= $book['available_quantity'] > 0 ? 'Available' : 'All Borrowed' ?>
                  </span>
                </td>
                <td>
                  <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/views/admin/books/show.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-secondary" title="View"><i class="fas fa-eye"></i></a>
                    <?php if (hasPermission('books.edit')): ?>
                    <a href="<?= BASE_URL ?>/views/admin/books/edit.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                    <?php endif; ?>
                    <?php if (hasPermission('books.delete')): ?>
                    <form method="POST" action="<?= BASE_URL ?>/views/admin/books/delete.php" style="display:inline;">
                      <?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= $book['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger" data-confirm="Delete '<?= e(addslashes($book['title'])) ?>'?"><i class="fas fa-trash"></i></button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="card-footer" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
          <small class="text-muted">
            Showing <?= $pagination['offset']+1 ?>–<?= min($pagination['offset']+$pagination['per_page'], $pagination['total']) ?> of <?= number_format($pagination['total']) ?>
          </small>
          <div class="pagination">
            <?php
              $q = http_build_query(array_merge($_GET, ['page' => max(1, $pagination['current_page'] - 1)]));
              $qn = http_build_query(array_merge($_GET, ['page' => min($pagination['total_pages'], $pagination['current_page'] + 1)]));
            ?>
            <a href="?<?= $q ?>" class="page-link <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>"><i class="fas fa-chevron-left"></i></a>
            <?php for ($p = max(1, $pagination['current_page']-2); $p <= min($pagination['total_pages'], $pagination['current_page']+2); $p++): ?>
              <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" class="page-link <?= $p === $pagination['current_page'] ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <a href="?<?= $qn ?>" class="page-link <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>"><i class="fas fa-chevron-right"></i></a>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
