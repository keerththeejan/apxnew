<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\BookingStatusLog;
use App\Models\Driver;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Services\VehicleBookingNotificationService;
use PDOException;

final class VehicleBookingsController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/vehicle-bookings');
    }

    public function index(): void
    {
        $this->requireAuth();
        $filters = [
            'q' => trim((string) Request::get('q', '')),
            'status' => trim((string) Request::get('status', '')),
            'vehicle_type' => trim((string) Request::get('vehicle_type', '')),
            'from' => trim((string) Request::get('from', '')),
            'to' => trim((string) Request::get('to', '')),
        ];
        $admin = $this->currentAdmin();
        $role = strtolower(trim((string) ($admin['role'] ?? '')));
        if ($role === 'driver') {
            $driver = Driver::findByEmail((string) ($admin['email'] ?? ''));
            $filters['driver_id'] = (int) ($driver['id'] ?? 0);
        }
        $page = (int) Request::get('page', 1);
        $result = VehicleBooking::paginate($filters, $page, 20);
        $s = Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.vehicle_bookings', [
            'title' => 'APX Admin - Vehicle Bookings',
            'pageKey' => 'vehicle_bookings',
            'pageTitle' => 'Vehicle Bookings',
            'crumb' => $siteName . ' / Vehicle Bookings',
            'filters' => $filters,
            'items' => $result['rows'],
            'page' => $result['page'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'schemaReady' => VehicleBooking::schemaReady(),
            'vehicles' => Vehicle::availableByType(''),
            'drivers' => Driver::available(),
            'statusCounts' => VehicleBooking::countsByStatus(),
            'statusLogClassReady' => BookingStatusLog::schemaReady(),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        if (!$this->canManageWrite()) {
            $_SESSION['flash_error'] = 'You do not have permission for this action.';
            $this->redirect('/admin/vehicle-bookings');

            return;
        }
        if (!$this->verifyCsrf()) {
            return;
        }
        $data = $this->payload();
        if ($data['pickup_location'] === '' || $data['pickup_datetime'] === '' || $data['customer_name'] === '' || $data['customer_phone'] === '') {
            $_SESSION['flash_error'] = 'Pickup, pickup time, customer name, and phone are required.';
            $this->redirect('/admin/vehicle-bookings');

            return;
        }
        try {
            $id = VehicleBooking::createWithInitialLog($data, isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'Created from admin');
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle_booking.create', 'vehicle_booking', $id, null);
            $row = VehicleBooking::findById($id);
            if (is_array($row)) {
                VehicleBookingNotificationService::sendForEvent($row, 'created');
            }
            $_SESSION['flash_success'] = 'Booking created.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = $this->isMissingTable($e) ? 'Run migrations for vehicle module first.' : 'Failed to create booking.';
        }
        $this->redirect('/admin/vehicle-bookings');
    }

    public function update(): void
    {
        $this->requireAuth();
        if (!$this->canManageWrite()) {
            $_SESSION['flash_error'] = 'You do not have permission for this action.';
            $this->redirect('/admin/vehicle-bookings');

            return;
        }
        if (!$this->verifyCsrf()) {
            return;
        }
        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid booking.';
            $this->redirect('/admin/vehicle-bookings');

            return;
        }
        $old = VehicleBooking::findById($id);
        if ($old === null) {
            $_SESSION['flash_error'] = 'Booking not found.';
            $this->redirect('/admin/vehicle-bookings');

            return;
        }
        $data = array_merge($old, $this->payload());
        VehicleBooking::updateById($id, $data);
        ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle_booking.update', 'vehicle_booking', $id, null);
        $_SESSION['flash_success'] = 'Booking updated.';
        $this->redirect('/admin/vehicle-bookings');
    }

    public function destroy(): void
    {
        $this->requireAuth();
        if (!$this->canManageWrite()) {
            $_SESSION['flash_error'] = 'You do not have permission for this action.';
            $this->redirect('/admin/vehicle-bookings');

            return;
        }
        if (!$this->verifyCsrf()) {
            return;
        }
        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid booking.';
            $this->redirect('/admin/vehicle-bookings');

            return;
        }
        VehicleBooking::delete($id);
        ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'vehicle_booking.delete', 'vehicle_booking', $id, null);
        $_SESSION['flash_success'] = 'Booking deleted.';
        $this->redirect('/admin/vehicle-bookings');
    }

    public function assign(): void
    {
        $this->requireAuth();
        if (!$this->canManageWrite()) {
            $_SESSION['flash_error'] = 'You do not have permission for this action.';
            $this->redirect('/admin/vehicle-bookings');

            return;
        }
        if (!$this->verifyCsrf()) {
            return;
        }
        $id = (int) Request::post('id', 0);
        $vehicleId = (int) Request::post('vehicle_id', 0);
        $driverId = (int) Request::post('driver_id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid booking.';
            $this->redirect('/admin/vehicle-bookings');

            return;
        }
        VehicleBooking::assignVehicleDriver($id, $vehicleId > 0 ? $vehicleId : null, $driverId > 0 ? $driverId : null, isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null);
        $row = VehicleBooking::findById($id);
        if (is_array($row)) {
            VehicleBookingNotificationService::sendForEvent($row, 'assigned');
        }
        $_SESSION['flash_success'] = 'Booking assignment updated.';
        $this->redirect('/admin/vehicle-bookings');
    }

    public function updateStatus(): void
    {
        $this->requireAuth();
        if (!$this->canManageWrite()) {
            $_SESSION['flash_error'] = 'You do not have permission for this action.';
            $this->redirect('/admin/vehicle-bookings');

            return;
        }
        if (!$this->verifyCsrf()) {
            return;
        }
        $id = (int) Request::post('id', 0);
        $status = trim((string) Request::post('status', 'pending'));
        if ($id < 1 || $status === '') {
            $_SESSION['flash_error'] = 'Invalid status update.';
            $this->redirect('/admin/vehicle-bookings');

            return;
        }
        VehicleBooking::updateStatus($id, $status, isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'Updated from admin');
        $row = VehicleBooking::findById($id);
        if (is_array($row)) {
            $event = match ($status) {
                'on_trip' => 'on_trip',
                'completed' => 'completed',
                default => 'status',
            };
            VehicleBookingNotificationService::sendForEvent($row, $event);
        }
        $_SESSION['flash_success'] = 'Booking status updated.';
        $this->redirect('/admin/vehicle-bookings');
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        $tripType = trim((string) Request::post('trip_type', 'one_way'));
        return [
            'booking_ref' => trim((string) Request::post('booking_ref', '')),
            'branch_id' => (int) Request::post('branch_id', 1),
            'vehicle_id' => (int) Request::post('vehicle_id', 0),
            'driver_id' => (int) Request::post('driver_id', 0),
            'booking_mode' => $tripType === 'rental' ? 'rental' : 'ride',
            'trip_type' => $tripType,
            'rental_unit' => trim((string) Request::post('rental_unit', 'hourly')),
            'vehicle_type' => trim((string) Request::post('vehicle_type', 'car')),
            'pickup_location' => trim((string) Request::post('pickup_location', '')),
            'drop_location' => trim((string) Request::post('drop_location', '')),
            'pickup_datetime' => trim((string) Request::post('pickup_datetime', '')),
            'return_datetime' => trim((string) Request::post('return_datetime', '')),
            'passenger_count' => (int) Request::post('passenger_count', 1),
            'luggage_count' => (int) Request::post('luggage_count', 0),
            'customer_name' => trim((string) Request::post('customer_name', '')),
            'customer_phone' => trim((string) Request::post('customer_phone', '')),
            'customer_email' => trim((string) Request::post('customer_email', '')),
            'customer_notes' => trim((string) Request::post('customer_notes', '')),
            'distance_km' => Request::post('distance_km', 0),
            'duration_minutes' => (int) Request::post('duration_minutes', 0),
            'estimated_total' => Request::post('estimated_total', 0),
            'currency_code' => trim((string) Request::post('currency_code', 'LKR')),
            'pricing_breakdown_json' => trim((string) Request::post('pricing_breakdown_json', '{}')),
            'status' => trim((string) Request::post('status', 'pending')),
        ];
    }

    private function verifyCsrf(): bool
    {
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';

            return false;
        }

        return true;
    }

    private function isMissingTable(PDOException $e): bool
    {
        $m = $e->getMessage();

        return str_contains($m, '42S02') || str_contains($m, "doesn't exist");
    }

    private function canManageWrite(): bool
    {
        $admin = $this->currentAdmin();
        $role = strtolower(trim((string) ($admin['role'] ?? '')));

        return $role !== 'driver';
    }
}
