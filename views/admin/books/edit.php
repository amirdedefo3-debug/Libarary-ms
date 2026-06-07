<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../controllers/BookController.php';
$ctrl = new BookController();
$ctrl->edit();
$pageTitle = 'Edit Book';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Edit Book</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/books/index.php">Books</a> / Edit</p>
        </div>
        <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div style="display:grid;grid-template-columns:1fr 280px;gap:20px;">
          <div>
            <div class="card mb-4">
              <div class="card-header">Basic Information</div>
              <div class="card-body">
                <div class="form-group">
                  <label>Book Title *</label>
                  <input type="text" name="title" class="form-control" required value="<?= e($_POST['title'] ?? $book['title']) ?>">
                </div>
                <div class="form-group">
                  <label>Subtitle</label>
                  <input type="text" name="subtitle" class="form-control" value="<?= e($_POST['subtitle'] ?? $book['subtitle']) ?>">
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" class="form-control" value="<?= e($_POST['isbn'] ?? $book['isbn']) ?>">
                  </div>
                  <div class="form-group">
                    <label>Edition</label>
                    <input type="text" name="edition" class="form-control" value="<?= e($_POST['edition'] ?? $book['edition']) ?>">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Author</label>
                    <select name="author_id" class="form-control">
                      <option value="">— Select —</option>
                      <?php foreach ($authors as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($book['author_id'] == $a['id']) ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Publisher</label>
                    <select name="publisher_id" class="form-control">
                      <option value="">— Select —</option>
                      <?php foreach ($publishers as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($book['publisher_id'] == $p['id']) ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" class="form-control">
                      <option value="">— Select —</option>
                      <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($book['category_id'] == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Language</label>
                    <select name="language" class="form-control">
                      <?php foreach (['English','Arabic','French','Spanish','German','Other'] as $lang): ?>
                        <option value="<?= $lang ?>" <?= $book['language'] === $lang ? 'selected' : '' ?>><?= $lang ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label>Description</label>
                  <textarea name="description" class="form-control"><?= e($book['description']) ?></textarea>
                </div>
              </div>
            </div>
            <div class="card">
              <div class="card-header">Library Details</div>
              <div class="card-body">
                <div class="form-row three">
                  <div class="form-group">
                    <label>Shelf</label>
                    <input type="text" name="shelf_number" class="form-control" value="<?= e($book['shelf_number']) ?>">
                  </div>
                  <div class="form-group">
                    <label>Rack</label>
                    <input type="text" name="rack_number" class="form-control" value="<?= e($book['rack_number']) ?>">
                  </div>
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?= $book['quantity'] ?>" min="1">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" class="form-control" step="0.01" value="<?= $book['price'] ?>">
                  </div>
                  <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control" value="<?= e($book['purchase_date']) ?>">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div>
            <div class="card">
              <div class="card-header">Cover Image</div>
              <div class="card-body" style="text-align:center;">
                <img id="coverPreview"
                     src="<?= BASE_URL ?>/uploads/books/<?= e($book['cover_image']) ?>"
                     onerror="this.src='<?= BASE_URL ?>/assets/images/default_book.png'"
                     style="width:140px;height:190px;object-fit:cover;border-radius:8px;border:2px solid var(--border);margin-bottom:12px;">
                <input type="file" name="cover_image" accept="image/*" class="form-control" data-preview="coverPreview">
                <p class="form-text">Leave blank to keep current</p>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Update Book</button>
          <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
