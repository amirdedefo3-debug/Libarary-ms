<?php
/**
 * Book Details View — included by BookController::show()
 * Variables: $book (array)
 */
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../config/config.php';
    require_once __DIR__ . '/../../../includes/middleware.php';
    require_once __DIR__ . '/../../../controllers/BookController.php';
    (new BookController())->show();
    exit;
}
$pageTitle = 'Book Details';
?>
<?php include BASE_PATH . '/includes/header.php'; ?>
<div class="wrapper">
  <?php include BASE_PATH . '/includes/sidebar.php'; ?>
  <div class="main-content">
    <?php include BASE_PATH . '/includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1 class="page-title">Book Details</h1>
          <p class="page-breadcrumb">
            <a href="<?= BASE_URL ?>/views/admin/books/index.php">Books</a> / View
          </p>
        </div>
        <div class="d-flex gap-2">
          <?php if (hasPermission('books.edit')): ?>
          <a href="<?= BASE_URL ?>/views/admin/books/edit.php?id=<?= $book['id'] ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
          </a>
          <?php endif; ?>
          <a href="<?= BASE_URL ?>/views/admin/books/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
          </a>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start;">
        <!-- Cover -->
        <div class="card">
          <div class="card-body" style="text-align:center;">
            <img src="<?= BASE_URL ?>/uploads/books/<?= e($book['cover_image']) ?>"
                 onerror="this.src='<?= BASE_URL ?>/assets/images/default_book.png'"
                 style="width:100%;max-width:200px;border-radius:8px;box-shadow:var(--shadow-md);margin-bottom:16px;">
            <div class="badge <?= $book['available_quantity'] > 0 ? 'badge-success' : 'badge-danger' ?>"
                 style="font-size:.9rem;padding:8px 16px;">
              <?= $book['available_quantity'] > 0 ? 'Available' : 'All Borrowed' ?>
            </div>
            <p style="margin-top:10px;font-size:.82rem;color:var(--text-muted);">
              <?= $book['available_quantity'] ?> of <?= $book['quantity'] ?> copies available
            </p>
          </div>
        </div>

        <!-- Details -->
        <div class="card">
          <div class="card-header">
            <i class="fas fa-book" style="color:var(--primary);margin-right:8px;"></i>
            <?= e($book['title']) ?>
          </div>
          <div class="card-body">
            <?php if ($book['subtitle']): ?>
              <p style="color:var(--text-muted);margin-bottom:16px;"><?= e($book['subtitle']) ?></p>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <?php foreach ([
                'ISBN'          => $book['isbn']           ?: '—',
                'Author'        => $book['author_name']    ?: '—',
                'Publisher'     => $book['publisher_name'] ?: '—',
                'Category'      => $book['category_name']  ?: '—',
                'Edition'       => $book['edition']        ?: '—',
                'Language'      => $book['language']       ?: 'English',
                'Shelf / Rack'  => ($book['shelf_number'] ?: '—') . ' / ' . ($book['rack_number'] ?: '—'),
                'Price'         => currency((float)$book['price']),
                'Purchase Date' => formatDate($book['purchase_date']),
                'Added'         => formatDate($book['created_at']),
              ] as $label => $value): ?>
              <div>
                <small class="text-muted"><?= $label ?></small>
                <p><strong><?= e($value) ?></strong></p>
              </div>
              <?php endforeach; ?>
            </div>

            <?php if ($book['description']): ?>
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
              <small class="text-muted">Description</small>
              <p style="margin-top:4px;"><?= nl2br(e($book['description'])) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($book['pdf_file']): ?>
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
              <a href="<?= BASE_URL ?>/uploads/pdfs/<?= e($book['pdf_file']) ?>"
                 target="_blank" class="btn btn-secondary">
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
      $bStmt = $db->prepare(
          "SELECT bt.issue_number, bt.issue_date, bt.due_date, bt.status, u.full_name
           FROM borrow_transactions bt
           JOIN members m ON bt.member_id=m.id
           JOIN users u ON m.user_id=u.id
           WHERE bt.book_id=?
           ORDER BY bt.created_at DESC LIMIT 10"
      );
      $bStmt->execute([$book['id']]);
      $borrowHistory = $bStmt->fetchAll();
      ?>
      <?php if (!empty($borrowHistory)): ?>
      <div class="card" style="margin-top:20px;">
        <div class="card-header">
          <i class="fas fa-history" style="color:var(--info);margin-right:8px;"></i>Borrow History
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr><th>Issue #</th><th>Member</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php foreach ($borrowHistory as $bh):
                $sc = ['borrowed'=>'badge-info','returned'=>'badge-success','overdue'=>'badge-danger','lost'=>'badge-warning'];
              ?>
              <tr>
                <td><small><?= e($bh['issue_number']) ?></small></td>
                <td><?= e($bh['full_name']) ?></td>
                <td><?= formatDate($bh['issue_date']) ?></td>
                <td><?= formatDate($bh['due_date']) ?></td>
                <td><span class="badge <?= $sc[$bh['status']] ?? 'badge-secondary' ?>"><?= ucfirst($bh['status']) ?></span></td>
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
<?php include BASE_PATH . '/includes/footer.php'; ?>
