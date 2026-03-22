<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\User;

final class UsersController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/users');
    }

    public function index(): void
    {
        $this->requireSuperAdmin();

        $q = trim((string) Request::get('q', ''));
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 15);
        $result = User::paginate($q, $page, $perPage);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.users', [
            'title' => 'APX Admin - Users',
            'pageKey' => 'users',
            'pageTitle' => 'Users',
            'crumb' => 'APX / Users',
            'q' => $q,
            'users' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ]);
    }

    public function toggleActive(): void
    {
        $this->requireSuperAdmin();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $id = (int) Request::post('id', 0);
        $active = (int) Request::post('is_active', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid user.';
            $this->redirect('/admin/users');
            return;
        }

        User::setActive($id, $active === 1 ? 1 : 0);
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'user.active', 'user', $id, ['is_active' => $active]);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'User updated.';
        $this->redirect('/admin/users');
    }

    public function updateRole(): void
    {
        $this->requireSuperAdmin();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $id = (int) Request::post('id', 0);
        $role = trim((string) Request::post('role', 'user'));
        if ($id < 1 || !in_array($role, ['user', 'staff', 'admin'], true)) {
            $_SESSION['flash_error'] = 'Invalid request.';
            $this->redirect('/admin/users');
            return;
        }

        User::setRole($id, $role);
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'user.role', 'user', $id, ['role' => $role]);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Role updated.';
        $this->redirect('/admin/users');
    }

    public function exportCsv(): void
    {
        $this->requireSuperAdmin();

        $q = trim((string) Request::get('q', ''));
        $result = User::paginate($q, 1, 5000);
        $rows = $result['rows'];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="users-' . gmdate('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }
        fputcsv($out, ['id', 'name', 'email', 'phone', 'locale', 'role', 'is_active', 'created_at']);
        foreach ($rows as $r) {
            fputcsv($out, [
                (string) ($r['id'] ?? ''),
                (string) ($r['name'] ?? ''),
                (string) ($r['email'] ?? ''),
                (string) ($r['phone'] ?? ''),
                (string) ($r['locale'] ?? ''),
                (string) ($r['role'] ?? 'user'),
                (string) ($r['is_active'] ?? ''),
                (string) ($r['created_at'] ?? ''),
            ]);
        }
        fclose($out);
        exit;
    }
}
