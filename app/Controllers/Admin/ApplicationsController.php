<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Services\WhatsAppService;

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
            'csrfToken' => \App\Core\Csrf::token(),
        ]);
    }

    public function updateStatus(): void
    {
        $this->requireAuth();
        $ajax = strtolower((string) Request::header('X-Requested-With', '')) === 'xmlhttprequest'
            || (string) Request::post('ajax', '') === '1';
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            if ($ajax) {
                http_response_code(419);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'message' => 'CSRF token mismatch'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(419);
                echo 'CSRF token mismatch';
            }
            return;
        }

        $id = (int) Request::post('id', 0);
        $status = trim((string) Request::post('status', ''));
        $returnTo = trim((string) Request::post('return_to', ''));
        if ($id < 1 || !in_array($status, ['pending', 'approved', 'rejected'], true)) {
            if ($ajax) {
                http_response_code(422);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'message' => 'Invalid request.'], JSON_UNESCAPED_UNICODE);

                return;
            }
            $_SESSION['flash_error'] = 'Invalid request.';
            if ($returnTo !== '' && str_starts_with($returnTo, '/admin/')) {
                $this->redirect($returnTo);
                return;
            }
            $this->redirect('/admin/applications');
            return;
        }

        Application::updateStatus($id, $status);
        $row = Application::findById($id);
        if ($row !== null) {
            try {
                $phone = (string) (($row['whatsapp_number'] ?? '') !== '' ? $row['whatsapp_number'] : ($row['phone'] ?? ''));
                if ($phone !== '') {
                    $msg = WhatsAppService::renderTemplate('whatsapp_tpl_status_update', [
                        'name' => (string) ($row['name'] ?? 'Customer'),
                        'status' => $status,
                        'service' => (string) ($row['service_type'] ?? ''),
                    ]);
                    WhatsAppService::sendText($phone, $msg, 'application.status', $id);
                }
            } catch (\Throwable) {
            }
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'application.status', 'application', $id, ['status' => $status]);
        } catch (\Throwable $e) {
        }
        if ($ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => true, 'message' => 'Status updated.'], JSON_UNESCAPED_UNICODE);

            return;
        }
        $_SESSION['flash_success'] = 'Status updated.';
        if ($returnTo !== '' && str_starts_with($returnTo, '/admin/')) {
            $this->redirect($returnTo);
            return;
        }
        $this->redirect('/admin/applications');
    }

    public function bulkWhatsapp(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => 'CSRF token mismatch'], JSON_UNESCAPED_UNICODE);

            return;
        }
        /** @var mixed $idsRaw */
        $idsRaw = Request::post('ids', []);
        $ids = [];
        if (is_array($idsRaw)) {
            foreach ($idsRaw as $one) {
                $v = (int) $one;
                if ($v > 0) {
                    $ids[] = $v;
                }
            }
        }
        $custom = trim((string) Request::post('message', ''));
        if ($ids === []) {
            http_response_code(422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => 'No records selected'], JSON_UNESCAPED_UNICODE);

            return;
        }
        $rows = Application::findManyByIds($ids);
        $sent = 0;
        foreach ($rows as $row) {
            $phone = (string) (($row['whatsapp_number'] ?? '') !== '' ? $row['whatsapp_number'] : ($row['phone'] ?? ''));
            if ($phone === '') {
                continue;
            }
            $msg = $custom !== '' ? $custom : WhatsAppService::renderTemplate('whatsapp_tpl_status_update', [
                'name' => (string) ($row['name'] ?? 'Customer'),
                'status' => (string) ($row['status'] ?? 'pending'),
                'service' => (string) ($row['service_type'] ?? ''),
            ]);
            $r = WhatsAppService::sendText($phone, $msg, 'application.bulk', (int) ($row['id'] ?? 0));
            if ($r['ok']) {
                $sent++;
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'message' => 'Bulk send complete', 'sent' => $sent, 'selected' => count($ids)], JSON_UNESCAPED_UNICODE);
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
