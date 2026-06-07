<?php
/**
 * Add Book View — included by BookController::create()
 * Variables: $error, $categories, $authors, $publishers
 */
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../config/config.php';
    require_once __DIR__ . '/../../../includes/middleware.php';
    require_once __DIR__ . '/../../../controllers/BookController.php';
    (new BookController())->create();
    exit;
}
$pageTitle = 'Add Book';
?>
<?php include BASE_PATH . '/includes/header.php'; ?>
<div class="wrapper">
  <?php include BASE_PATH . '/includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Add New Book</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/dashboard.php">Dashboard</a> / <a href="<?= BASE_URL ?>/views/admin/books/index.php">Books</a> / Add</p>
        </div>
        <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div style="display:grid;grid-template-columns:1fr 280px;gap:20px;">
          <!-- Left column -->
          <div>
            <div class="card mb-4">
              <div class="card-header">Basic Information</div>
              <div class="card-body">
                <div class="form-group">
                  <label>Book Title <span style="color:var(--danger)">*</span></label>
                  <input type="text" name="title" class="form-control" required value="<?= e($_POST['title'] ?? '') ?>" placeholder="Enter book title">
                </div>
                <div class="form-group">
                  <label>Subtitle</label>
                  <input type="text" name="subtitle" class="form-control" value="<?= e($_POST['subtitle'] ?? '') ?>" placeholder="Optional subtitle">
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" class="form-control" value="<?= e($_POST['isbn'] ?? '') ?>" placeholder="978-...">
                  </div>
                  <div class="form-group">
                    <label>Edition</label>
                    <input type="text" name="edition" class="form-control" value="<?= e($_POST['edition'] ?? '') ?>" placeholder="1st, 2nd...">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Author</label>
                    <select name="author_id" class="form-control">
                      <option value="">— Select Author —</option>
                      <?php foreach ($authors as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($_POST['author_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Publisher</label>
                    <select name="publisher_id" class="form-control">
                      <option value="">— Select Publisher —</option>
                      <?php foreach ($publishers as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($_POST['publisher_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" class="form-control">
                      <option value="">— Select Category —</option>
                      <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($_POST['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Language</label>
                    <select name="language" class="form-control">
                      <?php foreach (['English','Arabic','French','Spanish','German','Other'] as $lang): ?>
                        <option value="<?= $lang ?>" <?= ($_POST['language'] ?? 'English') === $lang ? 'selected' : '' ?>><?= $lang ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label>Description</label>
                  <textarea name="description" class="form-control"><?= e($_POST['description'] ?? '') ?></textarea>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header">Library Details</div>
              <div class="card-body">
                <div class="form-row three">
                  <div class="form-group">
                    <label>Shelf Number</label>
                    <input type="text" name="shelf_number" class="form-control" value="<?= e($_POST['shelf_number'] ?? '') ?>" placeholder="A1">
                  </div>
                  <div class="form-group">
                    <label>Rack Number</label>
                    <input type="text" name="rack_number" class="form-control" value="<?= e($_POST['rack_number'] ?? '') ?>" placeholder="R3">
                  </div>
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?= e($_POST['quantity'] ?? '1') ?>" min="1">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" class="form-control" step="0.01" value="<?= e($_POST['price'] ?? '0') ?>" placeholder="0.00">
                  </div>
                  <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control" value="<?= e($_POST['purchase_date'] ?? '') ?>">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right column -->
          <div>
            <div class="card mb-4">
              <div class="card-header">Cover Image</div>
              <div class="card-body" style="text-align:center;">
                <img id="coverPreview" src="<?= BASE_URL ?>/assets/images/default_book.png"
                     style="width:140px;height:190px;object-fit:cover;border-radius:8px;border:2px solid var(--border);margin-bottom:12px;">
                <input type="file" name="cover_image" accept="image/*" class="form-control" data-preview="coverPreview">
                <p class="form-text">JPEG, PNG. Max 5MB</p>
              </div>
            </div>
            <div class="card">
              <div class="card-header">PDF File</div>
              <div class="card-body">
                <input type="file" name="pdf_file" accept="application/pdf" class="form-control">
                <p class="form-text">PDF only. Max 5MB</p>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Book</button>
          <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>
