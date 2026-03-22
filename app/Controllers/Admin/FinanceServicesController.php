<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\FinanceService;
use PDOException;

final class FinanceServicesController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/finance');
    }

    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 12);
        $result = FinanceService::paginate($q, $page, $perPage);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $s = \App\Models\Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');

        view('admin.finance', [
            'title' => 'APX Admin - Finance Services',
            'pageKey' => 'finance',
            'pageTitle' => 'Finance Services',
            'crumb' => $siteName . ' / Finance',
            'q' => $q,
            'items' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'schemaReady' => FinanceService::schemaReady(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $data = [
            'title' => trim((string) Request::post('title', '')),
            'description' => trim((string) Request::post('description', '')),
            'status' => trim((string) Request::post('status', 'draft')),
            'sort_order' => (int) Request::post('sort_order', 0),
        ];
        if ($data['title'] === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            $this->redirect('/admin/finance');
            return;
        }
        if (!in_array($data['status'], ['draft', 'active'], true)) {
            $data['status'] = 'draft';
        }

        try {
            $id = FinanceService::create($data);
        } catch (PDOException $e) {
            if ($this->isMissingFinanceTable($e)) {
                $_SESSION['flash_error'] = $this->financeTableMissingMessage();
                $this->redirect('/admin/finance');
                return;
            }
            throw $e;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'finance_service.create', 'finance_service', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Finance service saved.';
        $this->redirect('/admin/finance');
    }

    public function update(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid id.';
            $this->redirect('/admin/finance');
            return;
        }

        $data = [
            'title' => trim((string) Request::post('title', '')),
            'description' => trim((string) Request::post('description', '')),
            'status' => trim((string) Request::post('status', 'draft')),
            'sort_order' => (int) Request::post('sort_order', 0),
        ];
        if ($data['title'] === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            $this->redirect('/admin/finance');
            return;
        }
        if (!in_array($data['status'], ['draft', 'active'], true)) {
            $data['status'] = 'draft';
        }

        try {
            FinanceService::update($id, $data);
        } catch (PDOException $e) {
            if ($this->isMissingFinanceTable($e)) {
                $_SESSION['flash_error'] = $this->financeTableMissingMessage();
                $this->redirect('/admin/finance');
                return;
            }
            throw $e;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'finance_service.update', 'finance_service', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Finance service updated.';
        $this->redirect('/admin/finance');
    }

    public function destroy(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid id.';
            $this->redirect('/admin/finance');
            return;
        }

        try {
            FinanceService::delete($id);
        } catch (PDOException $e) {
            if ($this->isMissingFinanceTable($e)) {
                $_SESSION['flash_error'] = $this->financeTableMissingMessage();
                $this->redirect('/admin/finance');
                return;
            }
            throw $e;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'finance_service.delete', 'finance_service', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Finance service removed.';
        $this->redirect('/admin/finance');
    }

    private function isMissingFinanceTable(PDOException $e): bool
    {
        $m = $e->getMessage();
        return str_contains($m, '42S02') || str_contains($m, "doesn't exist");
    }

    private function financeTableMissingMessage(): string
    {
        return 'The finance_services table is not in the database yet. From the project folder, run: php database/migrate.php up'
            . ' — or execute the finance_services CREATE TABLE from database/schema.sql in phpMyAdmin.';
    }
}
