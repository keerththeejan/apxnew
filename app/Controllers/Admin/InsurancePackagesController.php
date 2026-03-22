<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\InsurancePackage;
use PDOException;

final class InsurancePackagesController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/insurance');
    }

    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 12);
        $result = InsurancePackage::paginate($q, $page, $perPage);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $s = \App\Models\Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');

        view('admin.insurance', [
            'title' => 'APX Admin - Insurance Plans',
            'pageKey' => 'insurance',
            'pageTitle' => 'Insurance Services',
            'crumb' => $siteName . ' / Insurance',
            'q' => $q,
            'items' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
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
            'name' => trim((string) Request::post('name', '')),
            'summary' => trim((string) Request::post('summary', '')),
            'coverage_text' => trim((string) Request::post('coverage_text', '')),
            'price_from' => trim((string) Request::post('price_from', '')),
            'sort_order' => (int) Request::post('sort_order', 0),
            'is_active' => (int) Request::post('is_active', 1),
        ];
        if ($data['name'] === '') {
            $_SESSION['flash_error'] = 'Plan name is required.';
            $this->redirect('/admin/insurance');
            return;
        }
        $data['is_active'] = $data['is_active'] === 0 ? 0 : 1;

        try {
            $id = InsurancePackage::create($data);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = $this->missingTableMessage();
                $this->redirect('/admin/insurance');
                return;
            }
            throw $e;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'insurance_package.create', 'insurance_package', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Insurance plan saved.';
        $this->redirect('/admin/insurance');
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
            $this->redirect('/admin/insurance');
            return;
        }

        $data = [
            'name' => trim((string) Request::post('name', '')),
            'summary' => trim((string) Request::post('summary', '')),
            'coverage_text' => trim((string) Request::post('coverage_text', '')),
            'price_from' => trim((string) Request::post('price_from', '')),
            'sort_order' => (int) Request::post('sort_order', 0),
            'is_active' => (int) Request::post('is_active', 1),
        ];
        if ($data['name'] === '') {
            $_SESSION['flash_error'] = 'Plan name is required.';
            $this->redirect('/admin/insurance');
            return;
        }
        $data['is_active'] = $data['is_active'] === 0 ? 0 : 1;

        try {
            InsurancePackage::update($id, $data);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = $this->missingTableMessage();
                $this->redirect('/admin/insurance');
                return;
            }
            throw $e;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'insurance_package.update', 'insurance_package', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Insurance plan updated.';
        $this->redirect('/admin/insurance');
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
            $this->redirect('/admin/insurance');
            return;
        }

        try {
            InsurancePackage::delete($id);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = $this->missingTableMessage();
                $this->redirect('/admin/insurance');
                return;
            }
            throw $e;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'insurance_package.delete', 'insurance_package', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Insurance plan removed.';
        $this->redirect('/admin/insurance');
    }

    private function isMissingTable(PDOException $e): bool
    {
        $m = $e->getMessage();
        return str_contains($m, '42S02') || str_contains($m, "doesn't exist");
    }

    private function missingTableMessage(): string
    {
        return 'The insurance_packages table is missing. Import database/schema.sql or run migrations.';
    }
}
