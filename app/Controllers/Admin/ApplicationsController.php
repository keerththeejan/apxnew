<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Application;

final class ApplicationsController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/applications');
    }

    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $status = trim((string) Request::get('status', 'all'));
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 15);
        $result = Application::paginate($q, $status, $page, $perPage);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.applications', [
            'title' => 'APX Admin - Applications',
            'pageKey' => 'applications',
            'pageTitle' => 'Applications',
            'crumb' => 'APX / Applications',
            'q' => $q,
            'status' => $status,
            'applications' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ]);
    }

    public function updateStatus(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $id = (int) Request::post('id', 0);
        $status = trim((string) Request::post('status', ''));
        $returnTo = trim((string) Request::post('return_to', ''));
        if ($id < 1 || !in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $_SESSION['flash_error'] = 'Invalid request.';
            if ($returnTo !== '' && str_starts_with($returnTo, '/admin/')) {
                $this->redirect($returnTo);
                return;
            }
            $this->redirect('/admin/applications');
            return;
        }

        Application::updateStatus($id, $status);
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'application.status', 'application', $id, ['status' => $status]);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Status updated.';
        if ($returnTo !== '' && str_starts_with($returnTo, '/admin/')) {
            $this->redirect($returnTo);
            return;
        }
        $this->redirect('/admin/applications');
    }

    public function exportCsv(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $status = trim((string) Request::get('status', 'all'));
        $result = Application::paginate($q, $status, 1, 5000);
        $rows = $result['rows'];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="applications-' . gmdate('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }
        fputcsv($out, ['id', 'name', 'email', 'phone', 'service_type', 'status', 'created_at']);
        foreach ($rows as $r) {
            fputcsv($out, [
                (string) ($r['id'] ?? ''),
                (string) ($r['name'] ?? ''),
                (string) ($r['email'] ?? ''),
                (string) ($r['phone'] ?? ''),
                (string) ($r['service_type'] ?? ''),
                (string) ($r['status'] ?? ''),
                (string) ($r['created_at'] ?? ''),
            ]);
        }
        fclose($out);
        exit;
    }
}
