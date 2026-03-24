<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Models\VehicleAvailability;

final class VehicleAvailabilityController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/vehicle-availability');
    }

    public function index(): void
    {
        $this->requireAuth();
        $page = (int) Request::get('page', 1);
        $result = VehicleAvailability::paginate($page, 20);
        $s = Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.vehicle_availability', [
            'title' => 'APX Admin - Vehicle Availability',
            'pageKey' => 'vehicle_availability',
            'pageTitle' => 'Vehicle Availability',
            'crumb' => $siteName . ' / Vehicle Availability',
            'items' => $result['rows'],
            'page' => $result['page'],
            'pageCount' => $result['pageCount'],
            'vehicles' => Vehicle::allOrdered(),
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'schemaReady' => VehicleAvailability::schemaReady(),
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
        $vehicleId = (int) Request::post('vehicle_id', 0);
        $startAt = trim((string) Request::post('start_at', ''));
        $endAt = trim((string) Request::post('end_at', ''));
        $status = trim((string) Request::post('availability_status', 'blocked'));
        $notes = trim((string) Request::post('notes', ''));
        if ($vehicleId < 1 || $startAt === '' || $endAt === '') {
            $_SESSION['flash_error'] = 'Vehicle, start and end times are required.';
            $this->redirect('/admin/vehicle-availability');

            return;
        }
        if (VehicleAvailability::hasConflict($vehicleId, $startAt, $endAt, null)) {
            $_SESSION['flash_error'] = 'The selected slot conflicts with an existing reservation.';
            $this->redirect('/admin/vehicle-availability');

            return;
        }
        VehicleAvailability::reserve($vehicleId, null, $startAt, $endAt, $status, $notes);
        $_SESSION['flash_success'] = 'Availability block saved.';
        $this->redirect('/admin/vehicle-availability');
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
            $_SESSION['flash_error'] = 'Invalid availability item.';
            $this->redirect('/admin/vehicle-availability');

            return;
        }
        VehicleAvailability::delete($id);
        $_SESSION['flash_success'] = 'Availability item deleted.';
        $this->redirect('/admin/vehicle-availability');
    }
}
