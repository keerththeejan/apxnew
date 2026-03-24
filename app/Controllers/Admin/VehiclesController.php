<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\Vehicle;
use PDOException;

final class VehiclesController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/vehicles');
    }

    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $type = trim((string) Request::get('type', ''));
        $page = (int) Request::get('page', 1);
        $result = Vehicle::paginate($q, $type, $page, 15);

        $s = Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.vehicles', [
            'title' => 'APX Admin - Vehicles',
            'pageKey' => 'vehicles',
            'pageTitle' => 'Vehicles',
            'crumb' => $siteName . ' / Vehicles',
            'q' => $q,
            'type' => $type,
            'items' => $result['rows'],
            'page' => $result['page'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'schemaReady' => Vehicle::schemaReady(),
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
        if ($data['name'] === '' || $data['registration_number'] === '') {
            $_SESSION['flash_error'] = 'Vehicle name and registration number are required.';
            $this->redirect('/admin/vehicles');

            return;
        }
        try {
            $id = Vehicle::create($data);
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle.create', 'vehicle', $id, null);
            $_SESSION['flash_success'] = 'Vehicle created.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = $this->isMissingTable($e) ? 'Run migrations for vehicle module first.' : 'Failed to create vehicle.';
        }
        $this->redirect('/admin/vehicles');
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
            $_SESSION['flash_error'] = 'Invalid vehicle.';
            $this->redirect('/admin/vehicles');

            return;
        }
        $data = $this->payload();
        try {
            Vehicle::update($id, $data);
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle.update', 'vehicle', $id, null);
            $_SESSION['flash_success'] = 'Vehicle updated.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = $this->isMissingTable($e) ? 'Run migrations for vehicle module first.' : 'Failed to update vehicle.';
        }
        $this->redirect('/admin/vehicles');
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
            $_SESSION['flash_error'] = 'Invalid vehicle.';
            $this->redirect('/admin/vehicles');

            return;
        }
        Vehicle::delete($id);
        ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle.delete', 'vehicle', $id, null);
        $_SESSION['flash_success'] = 'Vehicle deleted.';
        $this->redirect('/admin/vehicles');
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'name' => trim((string) Request::post('name', '')),
            'vehicle_type' => trim((string) Request::post('vehicle_type', 'car')),
            'model' => trim((string) Request::post('model', '')),
            'registration_number' => trim((string) Request::post('registration_number', '')),
            'seating_capacity' => (int) Request::post('seating_capacity', 1),
            'luggage_capacity' => (int) Request::post('luggage_capacity', 0),
            'fuel_type' => trim((string) Request::post('fuel_type', '')),
            'image_path' => trim((string) Request::post('image_path', '')),
            'availability_status' => trim((string) Request::post('availability_status', 'available')),
            'branch_id' => (int) Request::post('branch_id', 1),
            'is_active' => (int) Request::post('is_active', 1),
            'pricing_json' => trim((string) Request::post('pricing_json', '{}')),
        ];
    }

    private function isMissingTable(PDOException $e): bool
    {
        $m = $e->getMessage();

        return str_contains($m, '42S02') || str_contains($m, "doesn't exist");
    }
}
