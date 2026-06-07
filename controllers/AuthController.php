<?php
/**
 * Authentication Controller
 */
require_once BASE_PATH . '/models/UserModel.php';

class AuthController {
    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login(): void {
        if (isLoggedIn()) {
            $this->redirectToDashboard();
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token. Please try again.';
            } else {
                $email    = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $remember = isset($_POST['remember']);

                $user = $this->userModel->findByEmail($email);

                if (!$user) {
                    $error = 'Invalid email or password.';
                } elseif ($user['status'] === 'suspended') {
                    $error = 'Your account has been suspended. Contact admin.';
                } elseif ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                    $mins = ceil((strtotime($user['locked_until']) - time()) / 60);
                    $error = "Account locked. Try again in $mins minute(s).";
                } elseif (!password_verify($password, $user['password'])) {
                    $this->userModel->incrementFailedAttempts($user['id']);
                    $this->userModel->logLogin($user['id'], 'failed');
                    $error = 'Invalid email or password.';
                } else {
                    // Success
                    $this->userModel->resetFailedAttempts($user['id']);
                    $this->userModel->updateLastLogin($user['id']);
                    $this->userModel->logLogin($user['id'], 'success');

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user']    = $user;
                    $_SESSION['permissions'] = $this->userModel->getPermissions($user['id']);

                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + 60 * 60 * 24 * 30, '/', '', false, true);
                        $this->userModel->update($user['id'], ['remember_token' => password_hash($token, PASSWORD_BCRYPT)]);
                    }

                    logActivity('login', 'auth', 'User logged in');
                    $this->redirectToDashboard();
                }
            }
        }

        include BASE_PATH . '/views/auth/login.php';
    }

    public function logout(): void {
        logActivity('logout', 'auth', 'User logged out');
        setcookie('remember_token', '', time() - 3600, '/');
        session_unset();
        session_destroy();
        redirect(BASE_URL . '/login.php');
    }

    public function forgotPassword(): void {
        $message = '';
        $error   = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid token.';
            } else {
                $email = trim($_POST['email'] ?? '');
                $user  = $this->userModel->findByEmail($email);
                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $this->userModel->setResetToken($user['id'], $token);
                    // In production, send email — for dev we output the link
                    $link = BASE_URL . '/reset-password.php?token=' . $token;
                    $message = "Password reset link (dev mode): <a href='$link'>$link</a>";
                } else {
                    // Don't reveal whether email exists
                    $message = 'If that email exists, a reset link has been sent.';
                }
            }
        }
        include BASE_PATH . '/views/auth/forgot_password.php';
    }

    public function resetPassword(): void {
        $token = $_GET['token'] ?? '';
        $user  = $this->userModel->findByResetToken($token);
        $error = '';
        $success = '';

        if (!$user) {
            $error = 'Invalid or expired reset link.';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid token.';
            } else {
                $pass    = $_POST['password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';
                if (strlen($pass) < 8) {
                    $error = 'Password must be at least 8 characters.';
                } elseif ($pass !== $confirm) {
                    $error = 'Passwords do not match.';
                } else {
                    $this->userModel->updatePassword($user['id'], $pass);
                    $this->userModel->clearResetToken($user['id']);
                    $success = 'Password updated. <a href="' . BASE_URL . '/login.php">Login now</a>';
                }
            }
        }
        include BASE_PATH . '/views/auth/reset_password.php';
    }

    private function redirectToDashboard(): void {
        $role = $_SESSION['user']['role_slug'] ?? 'member';
        $map  = [
            'super_admin' => '/views/admin/dashboard.php',
            'librarian'   => '/views/librarian/dashboard.php',
            'assistant'   => '/views/assistant/dashboard.php',
            'member'      => '/views/member/dashboard.php',
        ];
        redirect(BASE_URL . ($map[$role] ?? '/login.php'));
    }
}
