<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/middleware.php';
middleware(['member']);

require_once __DIR__ . '/../../models/MemberModel.php';
require_once __DIR__ . '/../../models/BookModel.php';
require_once __DIR__ . '/../../models/ReservationModel.php';

$memberModel      = new MemberModel();
$bookModel        = new BookModel();
$reservationModel = new ReservationModel();
$db               = Database::getInstance();

$member = $memberModel->findByUserId($_SESSION['user_id']);
if (!$member) redirect(BASE_URL . '/unauthorized.php');
$_SESSION['member'] = $member;

// Filters
$search     = trim($_GET['search'] ?? '');
$categoryId = $_GET['category_id'] ?? '';
$language   = $_GET['language'] ?? '';
$available  = $_GET['available'] ?? '';
$page       = max(1, (int)($_GET['page'] ?? 1));

$filters = [
    'search'      => $search,
    'category_id' => $categoryId,
    'language'    => $language,
    'available'   => $available,
];

$result     = $bookModel->getAll($page, 12, $filters);
$books      = $result['data'];
$pagination = paginate($result['total'], 12, $page);
$categories = $db->query("SELECT id,name FROM categories WHERE status='active' ORDER BY name")->fetchAll();

// Handle reserve
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_book_id'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $id = $reservationModel->create($member['id'], (int)$_POST['reserve_book_id']);
        setFlash($id ? 'success' : 'error', $id ? 'Book reserved! Check My Reservations.' : 'Already reserved or an error occurred.');
    }
    redirect(BASE_URL . '/views/member/search.php?' . http_build_query($_GET));
}

$pageTitle = 'Search Books';
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../includes/sidebar_member.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-content">
      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= $flash['type']==='success'?'success':'danger' ?>" data-auto-dismiss><?= e($flash['message']) ?></div>
      <?php endif; ?>

      <div class="page-header">
        <div>
          <h1 class="page-title">Search Books</h1>
          <p class="page-breadcrumb"><a href="<?= BASE_URL ?>/views/member/dashboard.php">Dashboard</a> / Search</p>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="card mb-4">
        <div class="card-body" style="padding:16px 20px;">
          <form method="GET">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
              <div style="flex:1;min-width:220px;position:relative;">
                <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Search</label>
                <i class="fas fa-search" style="position:absolute;left:12px;bottom:10px;color:var(--text-muted);"></i>
                <input type="text" name="search" value="<?= e($search) ?>" class="form-control" style="padding-left:36px;" placeholder="Title, Author, ISBN...">
              </div>
              <div style="min-width:160px;">
                <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Category</label>
                <select name="category_id" class="form-control">
                  <option value="">All Categories</option>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $categoryId==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div style="min-width:140px;">
                <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Language</label>
                <select name="language" class="form-control">
                  <option value="">Any Language</option>
                  <?php foreach (['English','Arabic','French','Spanish','German'] as $l): ?>
                    <option value="<?= $l ?>" <?= $language===$l?'selected':'' ?>><?= $l ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div style="min-width:130px;">
                <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Availability</label>
                <select name="available" class="form-control">
                  <option value="">All Books</option>
                  <option value="1" <?= $available==='1'?'selected':'' ?>>Available Only</option>
                </select>
              </div>
              <div style="display:flex;gap:8px;align-self:flex-end;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                <a href="<?= BASE_URL ?>/views/member/search.php" class="btn btn-secondary"><i class="fas fa-times"></i></a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Results count -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <p class="text-muted" style="font-size:.88rem;">
          <?php if ($search || $categoryId || $available): ?>
            Found <strong><?= number_format($pagination['total']) ?></strong> book<?= $pagination['total']!=1?'s':'' ?>
            <?= $search ? "for \"<strong>".e($search)."</strong>\"" : '' ?>
          <?php else: ?>
            Showing all books — <strong><?= number_format($pagination['total']) ?></strong> total
          <?php endif; ?>
        </p>
        <div style="display:flex;gap:8px;">
          <button id="viewGrid" class="btn btn-sm btn-primary" title="Grid view"><i class="fas fa-th"></i></button>
          <button id="viewList" class="btn btn-sm btn-secondary" title="List view"><i class="fas fa-list"></i></button>
        </div>
      </div>

      <!-- Book Grid -->
      <div id="bookGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
        <?php if (empty($books)): ?>
          <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--text-muted);">
            <i class="fas fa-search fa-3x" style="margin-bottom:16px;color:var(--border);"></i>
            <h3>No books found</h3>
            <p>Try a different search term or remove filters.</p>
            <a href="<?= BASE_URL ?>/views/member/search.php" class="btn btn-secondary mt-2">Clear Search</a>
          </div>
        <?php else: foreach ($books as $book): ?>
          <div class="book-card" style="background:var(--bg-card);border:1px solid var(--border);border-radius:10px;overflow:hidden;transition:all .2s;display:flex;flex-direction:column;">
            <div style="position:relative;">
              <img src="<?= BASE_URL ?>/uploads/books/<?= e($book['cover_image']) ?>"
                   onerror="this.src='<?= BASE_URL ?>/assets/images/default_book.png'"
                   style="width:100%;height:180px;object-fit:cover;">
              <div style="position:absolute;top:8px;right:8px;">
                <span class="badge <?= $book['available_quantity']>0?'badge-success':'badge-danger' ?>">
                  <?= $book['available_quantity']>0 ? 'Available' : 'Borrowed' ?>
                </span>
              </div>
            </div>
            <div style="padding:12px;flex:1;display:flex;flex-direction:column;">
              <div style="font-weight:600;font-size:.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:2px;" title="<?= e($book['title']) ?>"><?= e($book['title']) ?></div>
              <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:4px;"><?= e($book['author_name'] ?: 'Unknown Author') ?></div>
              <?php if ($book['category_name']): ?>
              <div style="margin-bottom:8px;">
                <span class="badge badge-secondary" style="font-size:.65rem;"><?= e($book['category_name']) ?></span>
              </div>
              <?php endif; ?>
              <div style="margin-top:auto;">
                <?php if ($book['available_quantity'] > 0): ?>
                  <div style="font-size:.78rem;color:var(--success);font-weight:600;">
                    <i class="fas fa-check-circle"></i> <?= $book['available_quantity'] ?> cop<?= $book['available_quantity']>1?'ies':'y' ?> available
                  </div>
                <?php else: ?>
                  <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="reserve_book_id" value="<?= $book['id'] ?>">
                    <button type="submit" class="btn btn-warning btn-sm w-100" style="margin-top:6px;">
                      <i class="fas fa-bookmark"></i> Reserve Book
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <!-- Pagination -->
      <?php if ($pagination['total_pages'] > 1): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <small class="text-muted">Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?> · <?= number_format($pagination['total']) ?> books</small>
        <div class="pagination">
          <a href="?<?= http_build_query(array_merge($_GET,['page'=>max(1,$pagination['current_page']-1)])) ?>"
             class="page-link <?= $pagination['current_page']<=1?'disabled':'' ?>"><i class="fas fa-chevron-left"></i></a>
          <?php for ($p = max(1,$pagination['current_page']-2); $p <= min($pagination['total_pages'],$pagination['current_page']+2); $p++): ?>
            <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>"
               class="page-link <?= $p===$pagination['current_page']?'active':'' ?>"><?= $p ?></a>
          <?php endfor; ?>
          <a href="?<?= http_build_query(array_merge($_GET,['page'=>min($pagination['total_pages'],$pagination['current_page']+1)])) ?>"
             class="page-link <?= $pagination['current_page']>=$pagination['total_pages']?'disabled':'' ?>"><i class="fas fa-chevron-right"></i></a>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>
<script>
// Grid / List toggle
document.getElementById('viewGrid')?.addEventListener('click', () => {
  const g = document.getElementById('bookGrid');
  g.style.gridTemplateColumns = 'repeat(auto-fill,minmax(180px,1fr))';
  document.querySelectorAll('.book-card').forEach(c => c.style.flexDirection = 'column');
  document.getElementById('viewGrid').className = 'btn btn-sm btn-primary';
  document.getElementById('viewList').className = 'btn btn-sm btn-secondary';
});
document.getElementById('viewList')?.addEventListener('click', () => {
  const g = document.getElementById('bookGrid');
  g.style.gridTemplateColumns = '1fr';
  document.querySelectorAll('.book-card').forEach(c => c.style.flexDirection = 'row');
  document.getElementById('viewList').className = 'btn btn-sm btn-primary';
  document.getElementById('viewGrid').className = 'btn btn-sm btn-secondary';
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
