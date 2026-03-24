<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Driver;
use App\Models\Setting;
use PDOException;

final class DriversController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/drivers');
    }

    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $status = trim((string) Request::get('status', ''));
        $page = (int) Request::get('page', 1);
        $result = Driver::paginate($q, $status, $page, 15);

        $s = Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.drivers', [
            'title' => 'APX Admin - Drivers',
            'pageKey' => 'drivers',
            'pageTitle' => 'Drivers',
            'crumb' => $siteName . ' / Drivers',
            'q' => $q,
            'status' => $status,
            'items' => $result['rows'],
            'page' => $result['page'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'schemaReady' => Driver::schemaReady(),
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
        $data = $this->payload();
        if ($data['name'] === '' || $data['phone'] === '' || $data['license_number'] === '') {
            $_SESSION['flash_error'] = 'Name, phone, and license are required.';
            $this->redirect('/admin/drivers');

            return;
        }
        try {
            $id = Driver::create($data);
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'driver.create', 'driver', $id, null);
            $_SESSION['flash_success'] = 'Driver created.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = $this->isMissingTable($e) ? 'Run migrations for vehicle module first.' : 'Failed to create driver.';
        }
        $this->redirect('/admin/drivers');
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
            $_SESSION['flash_error'] = 'Invalid driver.';
            $this->redirect('/admin/drivers');

            return;
        }
        $data = $this->payload();
        try {
            Driver::update($id, $data);
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'driver.update', 'driver', $id, null);
            $_SESSION['flash_success'] = 'Driver updated.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = $this->isMissingTable($e) ? 'Run migrations for vehicle module first.' : 'Failed to update driver.';
        }
        $this->redirect('/admin/drivers');
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
            $_SESSION['flash_error'] = 'Invalid driver.';
            $this->redirect('/admin/drivers');

            return;
        }
        Driver::delete($id);
        ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'driver.delete', 'driver', $id, null);
        $_SESSION['flash_success'] = 'Driver deleted.';
        $this->redirect('/admin/drivers');
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'name' => trim((string) Request::post('name', '')),
            'phone' => trim((string) Request::post('phone', '')),
            'email' => trim((string) Request::post('email', '')),
            'license_number' => trim((string) Request::post('license_number', '')),
            'profile_image_path' => trim((string) Request::post('profile_image_path', '')),
            'status' => trim((string) Request::post('status', 'available')),
            'vehicle_id' => (int) Request::post('vehicle_id', 0),
            'branch_id' => (int) Request::post('branch_id', 1),
            'is_active' => (int) Request::post('is_active', 1),
        ];
    }

    private function isMissingTable(PDOException $e): bool
    {
        $m = $e->getMessage();

        return str_contains($m, '42S02') || str_contains($m, "doesn't exist");
    }
}
