<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Services\VehicleAvailabilityService;
use App\Services\VehicleBookingNotificationService;
use App\Services\VehiclePricingService;

final class VehicleBookingController extends BaseController
{
    public function index(): void
    {
        $settings = Setting::allKeyed();
        if ((string) ($settings['vehicle_booking_module_enabled'] ?? '1') !== '1') {
            http_response_code(404);
            view('errors.404', ['title' => 'Not Found']);

            return;
        }
        view('pages.vehicle_booking', [
            'title' => 'Vehicle Booking',
            'metaDescription' => 'Book cars, vans, bikes and rides with live price estimate.',
            'mapApiKey' => (string) ($settings['google_maps_api_key'] ?? ''),
            'vehicleTypes' => [
                ['id' => 'car', 'label' => 'Car'],
                ['id' => 'van', 'label' => 'Van'],
                ['id' => 'suv', 'label' => 'SUV'],
                ['id' => 'bike', 'label' => 'Bike'],
                ['id' => 'luxury', 'label' => 'Luxury'],
            ],
        ]);
    }

    public function quote(): void
    {
        $payload = Request::isJson() ? Request::json() : $_POST;
        $input = [
            'vehicle_type' => (string) ($payload['vehicle_type'] ?? 'car'),
            'trip_type' => (string) ($payload['trip_type'] ?? 'one_way'),
            'rental_unit' => (string) ($payload['rental_unit'] ?? 'hourly'),
            'distance_km' => (float) ($payload['distance_km'] ?? 0),
            'duration_minutes' => (int) ($payload['duration_minutes'] ?? 0),
            'pickup_datetime' => (string) ($payload['pickup_datetime'] ?? ''),
            'waiting_minutes' => (int) ($payload['waiting_minutes'] ?? 0),
            'coupon_code' => (string) ($payload['coupon_code'] ?? ''),
            'branch_id' => (int) ($payload['branch_id'] ?? 1),
        ];

        $breakdown = VehiclePricingService::calculate($input);
        $startAt = (string) ($payload['pickup_datetime'] ?? '');
        $endAt = (string) ($payload['return_datetime'] ?? '');
        if ($endAt === '') {
            $mins = max(30, (int) ($input['duration_minutes'] ?: 60));
            $startTs = strtotime($startAt);
            $endAt = $startTs !== false ? date('Y-m-d H:i:s', $startTs + ($mins * 60)) : date('Y-m-d H:i:s', time() + 3600);
        }
        $availability = VehicleAvailabilityService::availableOptions((string) $input['vehicle_type'], $startAt, $endAt);

        $this->json(['ok' => true, 'breakdown' => $breakdown, 'available' => [
            'vehicles' => array_map(static fn (array $v): array => [
                'id' => (int) ($v['id'] ?? 0),
                'name' => (string) ($v['name'] ?? ''),
                'type' => (string) ($v['vehicle_type'] ?? ''),
                'seats' => (int) ($v['seating_capacity'] ?? 0),
                'luggage' => (int) ($v['luggage_capacity'] ?? 0),
            ], $availability['vehicles']),
        ]]);
    }

    public function checkAvailability(): void
    {
        $payload = Request::isJson() ? Request::json() : $_POST;
        $vehicleType = (string) ($payload['vehicle_type'] ?? '');
        $startAt = (string) ($payload['pickup_datetime'] ?? '');
        $endAt = (string) ($payload['return_datetime'] ?? '');
        if ($endAt === '') {
            $durationMin = max(30, (int) ($payload['duration_minutes'] ?? 60));
            $startTs = strtotime($startAt);
            $endAt = $startTs !== false ? date('Y-m-d H:i:s', $startTs + ($durationMin * 60)) : date('Y-m-d H:i:s', time() + 3600);
        }
        $availability = VehicleAvailabilityService::availableOptions($vehicleType, $startAt, $endAt);
        $this->json([
            'ok' => true,
            'vehicles' => $availability['vehicles'],
            'drivers' => $availability['drivers'],
        ]);
    }

    public function create(): void
    {
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';

            return;
        }

        $tripType = trim((string) Request::post('trip_type', 'one_way'));
        $payload = [
            'booking_mode' => $tripType === 'rental' ? 'rental' : 'ride',
            'trip_type' => $tripType,
            'rental_unit' => trim((string) Request::post('rental_unit', 'hourly')),
            'vehicle_type' => trim((string) Request::post('vehicle_type', 'car')),
            'pickup_location' => trim((string) Request::post('pickup_location', '')),
            'pickup_lat' => (string) Request::post('pickup_lat', ''),
            'pickup_lng' => (string) Request::post('pickup_lng', ''),
            'drop_location' => trim((string) Request::post('drop_location', '')),
            'drop_lat' => (string) Request::post('drop_lat', ''),
            'drop_lng' => (string) Request::post('drop_lng', ''),
            'pickup_datetime' => trim((string) Request::post('pickup_datetime', '')),
            'return_datetime' => trim((string) Request::post('return_datetime', '')),
            'passenger_count' => (int) Request::post('passenger_count', 1),
            'luggage_count' => (int) Request::post('luggage_count', 0),
            'customer_name' => trim((string) Request::post('customer_name', '')),
            'customer_phone' => trim((string) Request::post('customer_phone', '')),
            'customer_email' => trim((string) Request::post('customer_email', '')),
            'customer_notes' => trim((string) Request::post('customer_notes', '')),
            'currency_code' => 'LKR',
            'status' => 'pending',
            'branch_id' => 1,
            'otp_code' => (string) random_int(100000, 999999),
        ];

        if ($payload['pickup_location'] === '' || $payload['pickup_datetime'] === '' || $payload['customer_name'] === '' || $payload['customer_phone'] === '') {
            $_SESSION['flash_error'] = 'Please complete required fields.';
            $_SESSION['flash_old'] = $_POST;
            $this->redirect('/vehicle-booking');

            return;
        }

        $distanceKm = (float) Request::post('distance_km', 0);
        $durationMinutes = (int) Request::post('duration_minutes', 0);
        if ($distanceKm <= 0 && $payload['pickup_lat'] !== '' && $payload['pickup_lng'] !== '' && $payload['drop_lat'] !== '' && $payload['drop_lng'] !== '') {
            $distanceKm = $this->haversineKm((float) $payload['pickup_lat'], (float) $payload['pickup_lng'], (float) $payload['drop_lat'], (float) $payload['drop_lng']);
            $durationMinutes = max($durationMinutes, (int) round(($distanceKm / 32) * 60));
        }
        if ($distanceKm <= 0) {
            $distanceKm = 8.0;
        }
        if ($durationMinutes <= 0) {
            $durationMinutes = 45;
        }

        $pricing = VehiclePricingService::calculate([
            'vehicle_type' => $payload['vehicle_type'],
            'trip_type' => $payload['trip_type'],
            'rental_unit' => $payload['rental_unit'],
            'distance_km' => $distanceKm,
            'duration_minutes' => $durationMinutes,
            'pickup_datetime' => $payload['pickup_datetime'],
            'coupon_code' => trim((string) Request::post('coupon_code', '')),
            'branch_id' => 1,
        ]);
        $payload['distance_km'] = $distanceKm;
        $payload['duration_minutes'] = $durationMinutes;
        $payload['estimated_total'] = (float) ($pricing['total'] ?? 0);
        $payload['pricing_breakdown_json'] = json_encode($pricing, JSON_UNESCAPED_UNICODE);

        $id = VehicleBooking::createWithInitialLog($payload, null, 'Created from public booking page');
        $booking = VehicleBooking::findById($id);
        if (is_array($booking)) {
            VehicleBookingNotificationService::sendForEvent($booking, 'created');
        }

        $_SESSION['flash_success'] = 'Booking submitted successfully. Reference: ' . (VehicleBooking::findById($id)['booking_ref'] ?? '');
        $this->redirect('/vehicle-booking');
    }

    public function history(): void
    {
        $phone = trim((string) Request::get('phone', ''));
        $result = ['rows' => []];
        if ($phone !== '') {
            $result = VehicleBooking::paginate(['q' => $phone], 1, 50);
        }
        view('pages.vehicle_booking_history', [
            'title' => 'My Vehicle Bookings',
            'metaDescription' => 'View your vehicle booking history.',
            'phone' => $phone,
            'bookings' => $result['rows'],
        ]);
    }

    public function invoice(string $ref): void
    {
        $rows = VehicleBooking::paginate(['q' => $ref], 1, 1);
        $booking = $rows['rows'][0] ?? null;
        if (!is_array($booking)) {
            http_response_code(404);
            echo 'Invoice not found';

            return;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Invoice ' . e((string) ($booking['booking_ref'] ?? '')) . '</title>'
            . '<style>body{font-family:Arial,sans-serif;padding:24px;color:#0b1220}h1{margin:0 0 16px}table{border-collapse:collapse;width:100%;max-width:680px}td,th{border:1px solid #cbd5e1;padding:8px}th{text-align:left;background:#f8fafc}.tot{font-size:20px;font-weight:700;margin-top:12px}</style>'
            . '</head><body>'
            . '<h1>Vehicle Booking Invoice</h1>'
            . '<table><tr><th>Booking Ref</th><td>' . e((string) ($booking['booking_ref'] ?? '')) . '</td></tr>'
            . '<tr><th>Customer</th><td>' . e((string) ($booking['customer_name'] ?? '')) . '</td></tr>'
            . '<tr><th>Phone</th><td>' . e((string) ($booking['customer_phone'] ?? '')) . '</td></tr>'
            . '<tr><th>Trip</th><td>' . e((string) ($booking['trip_type'] ?? '')) . ' / ' . e((string) ($booking['vehicle_type'] ?? '')) . '</td></tr>'
            . '<tr><th>Pickup</th><td>' . e((string) ($booking['pickup_location'] ?? '')) . ' (' . e((string) ($booking['pickup_datetime'] ?? '')) . ')</td></tr>'
            . '<tr><th>Drop</th><td>' . e((string) ($booking['drop_location'] ?? '')) . '</td></tr>'
            . '<tr><th>Status</th><td>' . e((string) ($booking['status'] ?? '')) . '</td></tr>'
            . '</table>'
            . '<p class="tot">Total: ' . e((string) ($booking['currency_code'] ?? 'LKR')) . ' ' . e(number_format((float) ($booking['estimated_total'] ?? 0), 2)) . '</p>'
            . '<script>window.onload=function(){window.print();}</script>'
            . '</body></html>';
    }

    public function tracking(string $ref): void
    {
        $rows = VehicleBooking::paginate(['q' => $ref], 1, 1);
        $booking = $rows['rows'][0] ?? null;
        if (!is_array($booking)) {
            $this->json(['ok' => false, 'message' => 'Booking not found'], 404);

            return;
        }
        $pickLat = (float) ($booking['pickup_lat'] ?? 6.9271);
        $pickLng = (float) ($booking['pickup_lng'] ?? 79.8612);
        $dropLat = (float) ($booking['drop_lat'] ?? ($pickLat + 0.03));
        $dropLng = (float) ($booking['drop_lng'] ?? ($pickLng + 0.03));
        $created = strtotime((string) ($booking['created_at'] ?? 'now')) ?: time();
        $progress = min(1, max(0, (time() - $created) / 3600));
        $lat = $pickLat + (($dropLat - $pickLat) * $progress);
        $lng = $pickLng + (($dropLng - $pickLng) * $progress);

        $this->json([
            'ok' => true,
            'booking_ref' => (string) ($booking['booking_ref'] ?? ''),
            'status' => (string) ($booking['status'] ?? ''),
            'progress' => round($progress, 3),
            'position' => ['lat' => round($lat, 7), 'lng' => round($lng, 7)],
            'pickup' => ['lat' => $pickLat, 'lng' => $pickLng],
            'drop' => ['lat' => $dropLat, 'lng' => $dropLng],
        ]);
    }

    public function tracker(string $ref): void
    {
        view('pages.vehicle_booking_tracker', [
            'title' => 'Live Trip Tracking',
            'metaDescription' => 'Track your vehicle booking progress.',
            'bookingRef' => $ref,
        ]);
    }

    public function verifyOtp(): void
    {
        $bookingRef = trim((string) Request::post('booking_ref', ''));
        $otp = trim((string) Request::post('otp', ''));
        $matches = VehicleBooking::paginate(['q' => $bookingRef], 1, 1);
        $row = $matches['rows'][0] ?? null;
        if (!is_array($row)) {
            $this->json(['ok' => false, 'message' => 'Booking not found'], 404);

            return;
        }
        if ((string) ($row['otp_code'] ?? '') !== $otp) {
            $this->json(['ok' => false, 'message' => 'Invalid OTP'], 422);

            return;
        }
        VehicleBooking::updateStatus((int) ($row['id'] ?? 0), 'confirmed', null, 'OTP verified');
        $this->json(['ok' => true, 'message' => 'OTP verified']);
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth * $c;
    }
}
