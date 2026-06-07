<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
middleware(['super_admin']);

$db      = Database::getInstance();
$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    if ($action === 'backup') {
        // Create SQL backup via mysqldump (requires mysqldump in PATH)
        if (!is_dir(BACKUP_PATH)) mkdir(BACKUP_PATH, 0755, true);
        $filename = 'backup_' . date('Ymd_His') . '.sql';
        $filepath = BACKUP_PATH . $filename;
        $cmd = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s 2>&1',
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_NAME),
            escapeshellarg($filepath)
        );
        exec($cmd, $output, $returnCode);
        if ($returnCode === 0 && file_exists($filepath)) {
            $size = filesize($filepath);
            $db->prepare("INSERT INTO backups (filename,size,created_by) VALUES (?,?,?)")->execute([$filename,$size,$_SESSION['user_id']]);
            logActivity('backup', 'backup', "Created backup: $filename");
            $message = "Backup created: $filename";
        } else {
            $error = 'Backup failed. Ensure mysqldump is accessible. Output: ' . implode(' ', $output);
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['backup_id'] ?? 0);
        $row = $db->prepare("SELECT filename FROM backups WHERE id=?");
        $row->execute([$id]);
        $backup = $row->fetch();
        if ($backup) {
            @unlink(BACKUP_PATH . $backup['filename']);
            $db->prepare("DELETE FROM backups WHERE id=?")->execute([$id]);
            $message = 'Backup deleted.';
        }
    }
}

// Download
if (!empty($_GET['download'])) {
    $id = (int)$_GET['download'];
    $row = $db->prepare("SELECT filename FROM backups WHERE id=?");
    $row->execute([$id]);
    $backup = $row->fetch();
    if ($backup && file_exists(BACKUP_PATH . $backup['filename'])) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup['filename'] . '"');
        header('Content-Length: ' . filesize(BACKUP_PATH . $backup['filename']));
        readfile(BACKUP_PATH . $backup['filename']);
        exit;
    }
}

$backups   = $db->query("SELECT b.*, u.full_name FROM backups b LEFT JOIN users u ON b.created_by=u.id ORDER BY b.created_at DESC")->fetchAll();
$pageTitle = 'Database Backup';
?>
<?php include __DIR__ . '/../../../includes/header.php'; ?>
<div class="wrapper">
  <?php include __DIR__ . '/../../../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../../../includes/navbar.php'; ?>
    <div class="page-content">
      <div class="page-header"><h1 class="page-title">Database Backup</h1></div>
      <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>

      <div class="card mb-4">
        <div class="card-header">Create Backup</div>
        <div class="card-body" style="display:flex;gap:12px;align-items:center;">
          <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="backup">
            <button type="submit" class="btn btn-primary"><i class="fas fa-database"></i> Create Database Backup</button>
          </form>
          <p class="text-muted mb-0">Creates a full SQL dump of the database.</p>
        </div>
      </div>

      <div class="card">
        <div class="card-header">Backup History <span class="badge badge-primary"><?= count($backups) ?></span></div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead><tr><th>#</th><th>Filename</th><th>Size</th><th>Created By</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if (empty($backups)): ?>
              <tr><td colspan="6" class="text-center text-muted" style="padding:40px;">No backups yet.</td></tr>
              <?php else: foreach ($backups as $i => $b): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><i class="fas fa-file-code" style="color:var(--text-muted);margin-right:6px;"></i><?= e($b['filename']) ?></td>
                <td><?= number_format($b['size'] / 1024, 1) ?> KB</td>
                <td><?= e($b['full_name'] ?? 'System') ?></td>
                <td><?= formatDate($b['created_at'], 'd M Y H:i') ?></td>
                <td>
                  <div class="d-flex gap-2">
                    <a href="?download=<?= $b['id'] ?>" class="btn btn-sm btn-secondary"><i class="fas fa-download"></i> Download</a>
                    <form method="POST" style="display:inline;"><?= csrfField() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="backup_id" value="<?= $b['id'] ?>">
                      <button class="btn btn-sm btn-danger" data-confirm="Delete this backup?"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
