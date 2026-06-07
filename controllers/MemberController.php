<?php
/**
 * Member Controller — CRUD
 */
require_once BASE_PATH . '/models/MemberModel.php';
require_once BASE_PATH . '/models/UserModel.php';

class MemberController {
    private MemberModel $memberModel;
    private UserModel   $userModel;

    public function __construct() {
        $this->memberModel = new MemberModel();
        $this->userModel   = new UserModel();
    }

    public function index(): void {
        requirePermission('members.view');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $search  = trim($_GET['search'] ?? '');
        $result  = $this->memberModel->getAll($page, 20, $search);
        $members = $result['data'];
        $pagination = paginate($result['total'], 20, $page);
        include BASE_PATH . '/views/admin/members/index.php';
    }

    public function create(): void {
        requirePermission('members.add');
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid token.';
            } else {
                // Validate
                $email    = trim($_POST['email'] ?? '');
                $username = trim($_POST['username'] ?? '');
                $fullName = trim($_POST['full_name'] ?? '');
                $password = $_POST['password'] ?? 'Library@123';

                if (!$email || !$username || !$fullName) {
                    $error = 'Name, username and email are required.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Invalid email format.';
                } else {
                    // Handle photo
                    $photo = 'default.png';
                    if (!empty($_FILES['photo']['name'])) {
                        $up = uploadFile($_FILES['photo'], PROFILE_PHOTOS, ALLOWED_IMAGE_TYPES);
                        if ($up) $photo = $up;
                        else $error = 'Invalid photo file.';
                    }
                    if (!$error) {
                        $userId = $this->userModel->create([
                            'role_id'    => 4,
                            'username'   => $username,
                            'email'      => $email,
                            'password'   => $password,
                            'full_name'  => $fullName,
                            'phone'      => trim($_POST['phone'] ?? ''),
                            'gender'     => $_POST['gender'] ?? null,
                            'department' => trim($_POST['department'] ?? ''),
                            'address'    => trim($_POST['address'] ?? ''),
                        ]);
                        $this->userModel->update($userId, ['photo' => $photo]);
                        $this->memberModel->create($userId, [
                            'student_id'       => trim($_POST['student_id'] ?? ''),
                            'department'       => trim($_POST['department'] ?? ''),
                            'membership_date'  => $_POST['membership_date'] ?? date('Y-m-d'),
                            'expiry_date'      => $_POST['expiry_date'] ?? date('Y-m-d', strtotime('+1 year')),
                            'max_borrow_limit' => (int)($_POST['max_borrow_limit'] ?? 5),
                        ]);
                        logActivity('add_member', 'members', "Added member: $fullName");
                        setFlash('success', 'Member registered successfully.');
                        redirect(BASE_URL . '/views/admin/members/index.php');
                    }
                }
            }
        }
        include BASE_PATH . '/views/admin/members/create.php';
    }

    public function edit(): void {
        requirePermission('members.edit');
        $id     = (int)($_GET['id'] ?? 0);
        $member = $this->memberModel->findById($id);
        if (!$member) { setFlash('error', 'Member not found.'); redirect(BASE_URL . '/views/admin/members/index.php'); }
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid token.';
            } else {
                $uData = [
                    'full_name'  => trim($_POST['full_name'] ?? ''),
                    'phone'      => trim($_POST['phone'] ?? ''),
                    'gender'     => $_POST['gender'] ?? null,
                    'department' => trim($_POST['department'] ?? ''),
                    'address'    => trim($_POST['address'] ?? ''),
                ];
                if (!empty($_FILES['photo']['name'])) {
                    $up = uploadFile($_FILES['photo'], PROFILE_PHOTOS, ALLOWED_IMAGE_TYPES);
                    if ($up) $uData['photo'] = $up;
                }
                $this->userModel->update($member['user_id'], $uData);
                $this->memberModel->update($id, [
                    'student_id'       => trim($_POST['student_id'] ?? ''),
                    'membership_date'  => $_POST['membership_date'] ?? $member['membership_date'],
                    'expiry_date'      => $_POST['expiry_date'] ?? $member['expiry_date'],
                    'max_borrow_limit' => (int)($_POST['max_borrow_limit'] ?? 5),
                    'status'           => $_POST['status'] ?? 'active',
                ]);
                setFlash('success', 'Member updated successfully.');
                redirect(BASE_URL . '/views/admin/members/index.php');
            }
        }
        include BASE_PATH . '/views/admin/members/edit.php';
    }

    public function delete(): void {
        requirePermission('members.delete');
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid token.'); redirect(BASE_URL . '/views/admin/members/index.php');
        }
        $id = (int)($_POST['id'] ?? 0);
        $member = $this->memberModel->findById($id);
        if ($member) {
            $this->userModel->delete($member['user_id']);
            setFlash('success', 'Member removed.');
            logActivity('delete_member', 'members', 'Deleted member: ' . $member['full_name']);
        }
        redirect(BASE_URL . '/views/admin/members/index.php');
    }

    public function show(): void {
        requirePermission('members.view');
        $id     = (int)($_GET['id'] ?? 0);
        $member = $this->memberModel->findById($id);
        if (!$member) { setFlash('error', 'Member not found.'); redirect(BASE_URL . '/views/admin/members/index.php'); }
        include BASE_PATH . '/views/admin/members/show.php';
    }
}
