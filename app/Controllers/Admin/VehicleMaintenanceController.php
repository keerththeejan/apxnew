<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Models\VehicleMaintenanceLog;

final class VehicleMaintenanceController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/vehicle-maintenance');
    }

    public function index(): void
    {
        $this->requireAuth();
        $page = (int) Request::get('page', 1);
        $result = VehicleMaintenanceLog::paginate($page, 15);
        $s = Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.vehicle_maintenance', [
            'title' => 'APX Admin - Vehicle Maintenance',
            'pageKey' => 'vehicle_maintenance',
            'pageTitle' => 'Vehicle Maintenance',
            'crumb' => $siteName . ' / Vehicle Maintenance',
            'items' => $result['rows'],
            'page' => $result['page'],
            'pageCount' => $result['pageCount'],
            'vehicles' => Vehicle::allOrdered(),
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'schemaReady' => VehicleMaintenanceLog::schemaReady(),
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
            'vehicle_id' => (int) Request::post('vehicle_id', 0),
            'title' => trim((string) Request::post('title', '')),
            'details' => trim((string) Request::post('details', '')),
            'maintenance_date' => trim((string) Request::post('maintenance_date', date('Y-m-d'))),
            'next_due_date' => trim((string) Request::post('next_due_date', '')),
            'status' => trim((string) Request::post('status', 'scheduled')),
        ];
        if ($data['vehicle_id'] < 1 || $data['title'] === '') {
            $_SESSION['flash_error'] = 'Vehicle and title are required.';
            $this->redirect('/admin/vehicle-maintenance');

            return;
        }
        $id = VehicleMaintenanceLog::create($data);
        ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle_maintenance.create', 'vehicle_maintenance', $id, null);
        $_SESSION['flash_success'] = 'Maintenance log created.';
        $this->redirect('/admin/vehicle-maintenance');
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
            $_SESSION['flash_error'] = 'Invalid item.';
            $this->redirect('/admin/vehicle-maintenance');

            return;
        }
        VehicleMaintenanceLog::delete($id);
        ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle_maintenance.delete', 'vehicle_maintenance', $id, null);
        $_SESSION['flash_success'] = 'Maintenance log deleted.';
        $this->redirect('/admin/vehicle-maintenance');
    }
}
