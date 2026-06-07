<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
require_once __DIR__ . '/../../../controllers/BookController.php';
$ctrl = new BookController();
$ctrl->show();
$pageTitle = 'Book Details';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Book Details</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/admin/books/index.php">Books</a> / View</p>
        </div>
        <div class="d-flex gap-2">
          <?php if (hasPermission('books.edit')): ?>
          <a href="<?= BASE_URL ?>/views/admin/books/edit.php?id=<?= $book['id'] ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
          <?php endif; ?>
          <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:300px 1fr;gap:24px;align-items:start;">
        <!-- Cover Image -->
        <div class="card">
          <div class="card-body" style="text-align:center;">
            <img src="<?= BASE_URL ?>/uploads/books/<?= e($book['cover_image']) ?>"
                 onerror="this.src='<?= BASE_URL ?>/assets/images/default_book.png'"
                 style="width:100%;max-width:220px;border-radius:8px;box-shadow:var(--shadow-md);margin-bottom:16px;">
            <div class="badge <?= $book['available_quantity'] > 0 ? 'badge-success' : 'badge-danger' ?>" style="font-size:.9rem;padding:8px 16px;">
              <?= $book['available_quantity'] > 0 ? 'Available' : 'All Borrowed' ?>
            </div>
            <div style="margin-top:12px;font-size:.85rem;color:var(--text-muted);">
              <?= $book['available_quantity'] ?> of <?= $book['quantity'] ?> copies available
            </div>
          </div>
        </div>

        <!-- Book Details -->
        <div class="card">
          <div class="card-header">
            <span><i class="fas fa-book" style="color:var(--primary);margin-right:8px;"></i><?= e($book['title']) ?></span>
          </div>
          <div class="card-body">
            <?php if ($book['subtitle']): ?>
            <p style="color:var(--text-muted);margin-bottom:16px;font-size:1rem;"><?= e($book['subtitle']) ?></p>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div>
                <small class="text-muted">ISBN</small>
                <p><strong><?= e($book['isbn'] ?: '—') ?></strong></p>
              </div>
              <div>
                <small class="text-muted">Author</small>
                <p><strong><?= e($book['author_name'] ?: '—') ?></strong></p>
              </div>
              <div>
                <small class="text-muted">Publisher</small>
                <p><strong><?= e($book['publisher_name'] ?: '—') ?></strong></p>
              </div>
              <div>
                <small class="text-muted">Category</small>
                <p><strong><?= e($book['category_name'] ?: '—') ?></strong></p>
              </div>
              <div>
                <small class="text-muted">Edition</small>
                <p><?= e($book['edition'] ?: '—') ?></p>
              </div>
              <div>
                <small class="text-muted">Language</small>
                <p><?= e($book['language'] ?: 'English') ?></p>
              </div>
              <div>
                <small class="text-muted">Shelf / Rack</small>
                <p><?= e($book['shelf_number'] ?: '—') ?> / <?= e($book['rack_number'] ?: '—') ?></p>
              </div>
              <div>
                <small class="text-muted">Price</small>
                <p><?= currency((float)$book['price']) ?></p>
              </div>
              <div>
                <small class="text-muted">Purchase Date</small>
                <p><?= formatDate($book['purchase_date']) ?></p>
              </div>
              <div>
                <small class="text-muted">Added</small>
                <p><?= formatDate($book['created_at']) ?></p>
              </div>
            </div>

            <?php if ($book['description']): ?>
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
              <small class="text-muted">Description</small>
              <p style="margin-top:4px;"><?= nl2br(e($book['description'])) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($book['pdf_file']): ?>
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
              <a href="<?= BASE_URL ?>/uploads/pdfs/<?= e($book['pdf_file']) ?>" target="_blank" class="btn btn-secondary">
                <i class="fas fa-file-pdf"></i> View PDF
              </a>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Borrow History -->
      <?php
      $db = Database::getInstance();
      $borrows = $db->prepare(
          "SELECT bt.issue_number, bt.issue_date, bt.due_date, bt.status, u.full_name
           FROM borrow_transactions bt
           JOIN members m ON bt.member_id=m.id
           JOIN users u ON m.user_id=u.id
           WHERE bt.book_id=?
           ORDER BY bt.created_at DESC LIMIT 10"
      );
      $borrows->execute([$book['id']]);
      $borrowHistory = $borrows->fetchAll();
      ?>
      <?php if (!empty($borrowHistory)): ?>
      <div class="card" style="margin-top:20px;">
        <div class="card-header"><i class="fas fa-history" style="color:var(--info);margin-right:8px;"></i>Borrow History</div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead><tr><th>Issue #</th><th>Member</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($borrowHistory as $bh): ?>
              <tr>
                <td><small><?= e($bh['issue_number']) ?></small></td>
                <td><?= e($bh['full_name']) ?></td>
                <td><?= formatDate($bh['issue_date']) ?></td>
                <td><?= formatDate($bh['due_date']) ?></td>
                <td>
                  <?php $sc=['borrowed'=>'badge-info','returned'=>'badge-success','overdue'=>'badge-danger','lost'=>'badge-warning']; ?>
                  <span class="badge <?= $sc[$bh['status']] ?? 'badge-secondary' ?>"><?= ucfirst($bh['status']) ?></span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
