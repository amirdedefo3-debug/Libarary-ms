<?php
/**
 * Book Controller — CRUD + Search
 */
require_once BASE_PATH . '/models/BookModel.php';

class BookController {
    private BookModel $model;

    public function __construct() {
        $this->model = new BookModel();
    }

    public function index(): void {
        requirePermission('books.view');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $filters = [
            'search'      => trim($_GET['search'] ?? ''),
            'category_id' => $_GET['category_id'] ?? '',
            'language'    => $_GET['language'] ?? '',
            'available'   => $_GET['available'] ?? '',
        ];
        $result = $this->model->getAll($page, $perPage, $filters);
        $books  = $result['data'];
        $pagination = paginate($result['total'], $perPage, $page);

        // Categories for filter dropdown
        $db = Database::getInstance();
        $categories = $db->query("SELECT id,name FROM categories WHERE status='active' ORDER BY name")->fetchAll();

        include BASE_PATH . '/views/admin/books/index.php';
    }

    public function create(): void {
        requirePermission('books.add');
        $db         = Database::getInstance();
        $categories = $db->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
        $authors    = $db->query("SELECT id,name FROM authors ORDER BY name")->fetchAll();
        $publishers = $db->query("SELECT id,name FROM publishers ORDER BY name")->fetchAll();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid token.';
            } else {
                $data = [
                    'isbn'          => trim($_POST['isbn'] ?? ''),
                    'title'         => trim($_POST['title'] ?? ''),
                    'subtitle'      => trim($_POST['subtitle'] ?? ''),
                    'author_id'     => (int)($_POST['author_id'] ?? 0) ?: null,
                    'publisher_id'  => (int)($_POST['publisher_id'] ?? 0) ?: null,
                    'category_id'   => (int)($_POST['category_id'] ?? 0) ?: null,
                    'edition'       => trim($_POST['edition'] ?? ''),
                    'language'      => trim($_POST['language'] ?? 'English'),
                    'shelf_number'  => trim($_POST['shelf_number'] ?? ''),
                    'rack_number'   => trim($_POST['rack_number'] ?? ''),
                    'quantity'      => max(1, (int)($_POST['quantity'] ?? 1)),
                    'price'         => (float)($_POST['price'] ?? 0),
                    'purchase_date' => $_POST['purchase_date'] ?? null,
                    'description'   => trim($_POST['description'] ?? ''),
                ];

                if (empty($data['title'])) {
                    $error = 'Book title is required.';
                } else {
                    // Handle cover image upload
                    if (!empty($_FILES['cover_image']['name'])) {
                        $img = uploadFile($_FILES['cover_image'], BOOK_COVERS, ALLOWED_IMAGE_TYPES);
                        if ($img) $data['cover_image'] = $img;
                        else $error = 'Invalid image file.';
                    }
                    // Handle PDF upload
                    if (!empty($_FILES['pdf_file']['name'])) {
                        $pdf = uploadFile($_FILES['pdf_file'], BOOK_PDFS, ALLOWED_PDF_TYPES);
                        if ($pdf) $data['pdf_file'] = $pdf;
                        else $error = 'Invalid PDF file.';
                    }

                    if (!$error) {
                        $id = $this->model->create($data);
                        logActivity('add_book', 'books', 'Added book: ' . $data['title']);
                        setFlash('success', 'Book added successfully.');
                        redirect(BASE_URL . '/views/admin/books/index.php');
                    }
                }
            }
        }
        include BASE_PATH . '/views/admin/books/create.php';
    }

    public function edit(): void {
        requirePermission('books.edit');
        $id   = (int)($_GET['id'] ?? 0);
        $book = $this->model->findById($id);
        if (!$book) { setFlash('error', 'Book not found.'); redirect(BASE_URL . '/views/admin/books/index.php'); }

        $db         = Database::getInstance();
        $categories = $db->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
        $authors    = $db->query("SELECT id,name FROM authors ORDER BY name")->fetchAll();
        $publishers = $db->query("SELECT id,name FROM publishers ORDER BY name")->fetchAll();
        $error      = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid token.';
            } else {
                $data = [
                    'isbn'         => trim($_POST['isbn'] ?? ''),
                    'title'        => trim($_POST['title'] ?? ''),
                    'subtitle'     => trim($_POST['subtitle'] ?? ''),
                    'author_id'    => (int)($_POST['author_id'] ?? 0) ?: null,
                    'publisher_id' => (int)($_POST['publisher_id'] ?? 0) ?: null,
                    'category_id'  => (int)($_POST['category_id'] ?? 0) ?: null,
                    'edition'      => trim($_POST['edition'] ?? ''),
                    'language'     => trim($_POST['language'] ?? 'English'),
                    'shelf_number' => trim($_POST['shelf_number'] ?? ''),
                    'rack_number'  => trim($_POST['rack_number'] ?? ''),
                    'quantity'     => max(1, (int)($_POST['quantity'] ?? 1)),
                    'price'        => (float)($_POST['price'] ?? 0),
                    'purchase_date'=> $_POST['purchase_date'] ?? null,
                    'description'  => trim($_POST['description'] ?? ''),
                ];

                if (!empty($_FILES['cover_image']['name'])) {
                    $img = uploadFile($_FILES['cover_image'], BOOK_COVERS, ALLOWED_IMAGE_TYPES);
                    if ($img) $data['cover_image'] = $img;
                    else $error = 'Invalid image.';
                }
                if (!$error) {
                    $this->model->update($id, $data);
                    logActivity('edit_book', 'books', 'Edited book: ' . $data['title']);
                    setFlash('success', 'Book updated successfully.');
                    redirect(BASE_URL . '/views/admin/books/index.php');
                }
            }
        }
        include BASE_PATH . '/views/admin/books/edit.php';
    }

    public function delete(): void {
        requirePermission('books.delete');
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid token.'); redirect(BASE_URL . '/views/admin/books/index.php');
        }
        $id = (int)($_POST['id'] ?? 0);
        $book = $this->model->findById($id);
        if ($book) {
            $this->model->delete($id);
            logActivity('delete_book', 'books', 'Deleted book: ' . $book['title']);
            setFlash('success', 'Book deleted.');
        }
        redirect(BASE_URL . '/views/admin/books/index.php');
    }

    public function show(): void {
        requirePermission('books.view');
        $id   = (int)($_GET['id'] ?? 0);
        $book = $this->model->findById($id);
        if (!$book) { setFlash('error', 'Book not found.'); redirect(BASE_URL . '/views/admin/books/index.php'); }
        include BASE_PATH . '/views/admin/books/show.php';
    }
}
