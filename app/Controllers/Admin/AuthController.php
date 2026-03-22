<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\AdminLoginAttempt;
use App\Models\AdminPasswordReset;
use App\Services\Mailer;
use App\Services\SiteConfig;

final class AuthController extends BaseController
{
    public function loginHtmlAlias(): void
    {
        $this->redirect('/admin/login');
    }

    public function showLogin(): void
    {
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        view('admin.login', [
            'flashError' => $flashError,
        ]);
    }

    public function login(): void
    {
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $email = strtolower(trim((string) Request::post('email', '')));
        $password = trim((string) Request::post('password', ''));
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        $maxAttempts = 5;
        $lockMinutes = 15;
        try {
            $maxAttempts = max(1, min(50, (int) SiteConfig::get('login_max_attempts', '5')));
            $lockMinutes = max(1, min(1440, (int) SiteConfig::get('login_lockout_minutes', '15')));
        } catch (\Throwable $e) {
        }

        try {
            $n = AdminLoginAttempt::countRecent($email, $ip, $lockMinutes);
            if ($n >= $maxAttempts) {
                $_SESSION['flash_error'] = 'Too many login attempts. Please try again later.';
                $this->redirect('/admin/login');
                return;
            }
        } catch (\Throwable $e) {
        }

        $admin = Admin::findByEmail($email);
        if ($admin === null || !password_verify($password, (string) ($admin['password_hash'] ?? ''))) {
            try {
                AdminLoginAttempt::record($email, $ip);
            } catch (\Throwable $e) {
            }
            $_SESSION['flash_error'] = 'Invalid login details.';
            $this->redirect('/admin/login');
            return;
        }

        $_SESSION['admin_id'] = (int) $admin['id'];
        try {
            ActivityLog::record((int) $admin['id'], 'admin.login', 'admin', (int) $admin['id'], ['ip' => $ip]);
        } catch (\Throwable $e) {
        }

        $this->redirect('/admin');
    }

    public function showForgotPassword(): void
    {
        $flashError = $_SESSION['flash_error'] ?? null;
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        view('admin.forgot_password', [
            'flashError' => $flashError,
            'flashSuccess' => $flashSuccess,
        ]);
    }

    public function forgotPassword(): void
    {
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $email = strtolower(trim((string) Request::post('email', '')));
        $admin = Admin::findByEmail($email);
        if ($admin !== null) {
            $token = bin2hex(random_bytes(32));
            try {
                AdminPasswordReset::createForAdmin((int) $admin['id'], $token);
                $link = base_url('/admin/reset-password?token=' . rawurlencode($token));
                $body = "Reset your admin password:\n\n{$link}\n\nThis link expires in one hour.";
                Mailer::send((string) $admin['email'], 'Password reset', $body);
            } catch (\Throwable $e) {
            }
        }

        $_SESSION['flash_success'] = 'If an account exists for that email, reset instructions have been sent.';
        $this->redirect('/admin/forgot-password');
    }

    public function showResetPassword(): void
    {
        $token = (string) Request::get('token', '');
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        view('admin.reset_password', [
            'token' => $token,
            'flashError' => $flashError,
        ]);
    }

    public function resetPassword(): void
    {
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $token = (string) Request::post('token', '');
        $pass = (string) Request::post('password', '');
        $pass2 = (string) Request::post('password_confirmation', '');

        if (strlen($pass) < 8 || $pass !== $pass2) {
            $_SESSION['flash_error'] = 'Passwords must match and be at least 8 characters.';
            $this->redirect('/admin/reset-password?token=' . rawurlencode($token));
            return;
        }

        $row = AdminPasswordReset::consume($token);
        if ($row === null) {
            $_SESSION['flash_error'] = 'Invalid or expired reset link.';
            $this->redirect('/admin/login');
            return;
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        Admin::updatePasswordHash($row['id'], $hash);
        $_SESSION['flash_success'] = 'Password updated. You can sign in now.';
        $this->redirect('/admin/login');
    }

    public function logout(): void
    {
        unset($_SESSION['admin_id']);
        $this->redirect('/admin/login');
    }
}
