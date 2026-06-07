<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/middleware.php';
requirePermission('reservations.manage');
require_once __DIR__ . '/../../../models/ReservationModel.php';

$resModel = new ReservationModel();
$page     = max(1, (int)($_GET['page'] ?? 1));
$status   = $_GET['status'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $id  = (int)($_POST['id'] ?? 0);
    $act = $_POST['action'] ?? '';
    if (in_array($act, ['approved','rejected','collected','cancelled'])) {
        $resModel->updateStatus($id, $act, $_SESSION['user_id']);
        // Notify member
        $res = $resModel->findById($id);
        if ($res) {
            $msgs = [
                'approved'  => 'Your reservation for "' . $res['book_title'] . '" has been approved.',
                'rejected'  => 'Your reservation for "' . $res['book_title'] . '" was rejected.',
                'collected' => 'You have collected "' . $res['book_title'] . '".',
                'cancelled' => 'Reservation for "' . $res['book_title'] . '" has been cancelled.',
            ];
            // Get user_id from member
            $db = Database::getInstance();
            $uid = $db->prepare("SELECT user_id FROM members WHERE id=?");
            $uid->execute([$res['member_id']]);
            $userId = $uid->fetchColumn();
            if ($userId) sendNotification($userId, ucfirst($act) . ' — Reservation', $msgs[$act], 'reservation');
        }
        logActivity($act . '_reservation', 'reservations', "Reservation #$id $act");
        setFlash('success', 'Reservation ' . $act . '.');
    }
    header('Location: ' . BASE_URL . '/views/admin/reservations/index.php?status=' . $status);
    exit;
}

$result = $resModel->getAll($page, 20, $status);
$reservations = $result['data'];
$pagination   = paginate($result['total'], 20, $page);
$pageTitle    = 'Reservations';
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
        <h1 class="page-title">Reservations</h1>
      </div>

      <!-- Tabs -->
      <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <?php foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'collected' => 'Collected', 'cancelled' => 'Cancelled', 'rejected' => 'Rejected'] as $v => $l): ?>
          <a href="?status=<?= $v ?>" class="btn btn-sm <?= $status===$v?'btn-primary':'btn-secondary' ?>"><?= $l ?></a>
        <?php endforeach; ?>
      </div>

      <div class="card">
        <div class="card-header">Reservations <span class="badge badge-primary"><?= number_format($pagination['total']) ?></span></div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
          <table>
            <thead>
              <tr><th>#</th><th>Member</th><th>Book</th><th>Reserved</th><th>Expires</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php if (empty($reservations)): ?>
              <tr><td colspan="7" class="text-center text-muted" style="padding:40px;">No reservations found.</td></tr>
              <?php else: foreach ($reservations as $i => $r): ?>
              <tr>
                <td><?= $pagination['offset']+$i+1 ?></td>
                <td><?= e($r['member_name']) ?><br><small class="text-muted"><?= e($r['member_code']) ?></small></td>
                <td><?= e($r['book_title']) ?></td>
                <td><?= formatDate($r['reserved_date']) ?></td>
                <td><?= formatDate($r['expiry_date']) ?></td>
                <td>
                  <?php $sc=['pending'=>'badge-warning','approved'=>'badge-info','collected'=>'badge-success','cancelled'=>'badge-secondary','rejected'=>'badge-danger']; ?>
                  <span class="badge <?= $sc[$r['status']] ?? 'badge-secondary' ?>"><?= ucfirst($r['status']) ?></span>
                </td>
                <td>
                  <?php if ($r['status'] === 'pending'): ?>
                  <div class="d-flex gap-2">
                    <form method="POST" style="display:inline;"><?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <input type="hidden" name="action" value="approved">
                      <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                    </form>
                    <form method="POST" style="display:inline;"><?= csrfField() ?>
                      <input type="hidden" name="id" value="<?= $r['id'] ?>">
                      <input type="hidden" name="action" value="rejected">
                      <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Reject</button>
                    </form>
                  </div>
                  <?php elseif ($r['status'] === 'approved'): ?>
                  <form method="POST" style="display:inline;"><?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="action" value="collected">
                    <button class="btn btn-sm btn-primary"><i class="fas fa-box"></i> Collected</button>
                  </form>
                  <?php else: ?>
                    <span class="text-muted" style="font-size:.8rem;">—</span>
                  <?php endif; ?>
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
