<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BookingCoupon;
use App\Models\PricingRule;

final class VehiclePricingService
{
    /**
     * @param array<string,mixed> $input
     * @return array<string,mixed>
     */
    public static function calculate(array $input): array
    {
        $vehicleType = trim((string) ($input['vehicle_type'] ?? 'car'));
        $tripType = trim((string) ($input['trip_type'] ?? 'one_way'));
        $rentalUnit = trim((string) ($input['rental_unit'] ?? 'hourly'));
        $distanceKm = max(0.0, (float) ($input['distance_km'] ?? 0));
        $durationMinutes = max(0, (int) ($input['duration_minutes'] ?? 0));
        $pickupDatetime = trim((string) ($input['pickup_datetime'] ?? ''));
        $waitingMinutes = max(0, (int) ($input['waiting_minutes'] ?? 0));
        $branchId = (int) ($input['branch_id'] ?? 0);

        $rule = PricingRule::findActiveByType($vehicleType, $branchId > 0 ? $branchId : null);
        $defaults = [
            'base_fare' => 300.0,
            'per_km' => 90.0,
            'per_hour' => 750.0,
            'per_day' => 3500.0,
            'waiting_per_hour' => 250.0,
            'extra_km_charge' => 100.0,
            'extra_km_threshold' => 40.0,
            'night_charge_percent' => 0.0,
            'peak_charge_percent' => 0.0,
            'peak_start' => null,
            'peak_end' => null,
            'night_start' => null,
            'night_end' => null,
        ];
        if ($rule !== null) {
            $defaults = array_merge($defaults, [
                'base_fare' => (float) ($rule['base_fare'] ?? 0),
                'per_km' => (float) ($rule['per_km'] ?? 0),
                'per_hour' => (float) ($rule['per_hour'] ?? 0),
                'per_day' => (float) ($rule['per_day'] ?? 0),
                'waiting_per_hour' => (float) ($rule['waiting_per_hour'] ?? 0),
                'extra_km_charge' => (float) ($rule['extra_km_charge'] ?? 0),
                'extra_km_threshold' => (float) ($rule['extra_km_threshold'] ?? 0),
                'night_charge_percent' => (float) ($rule['night_charge_percent'] ?? 0),
                'peak_charge_percent' => (float) ($rule['peak_charge_percent'] ?? 0),
                'peak_start' => $rule['peak_start'] ?? null,
                'peak_end' => $rule['peak_end'] ?? null,
                'night_start' => $rule['night_start'] ?? null,
                'night_end' => $rule['night_end'] ?? null,
            ]);
        }

        $baseFare = $defaults['base_fare'];
        $distanceFare = $distanceKm * $defaults['per_km'];
        $durationHours = $durationMinutes / 60;
        $rentalFare = 0.0;
        if ($tripType === 'rental') {
            $rentalFare = $rentalUnit === 'daily'
                ? ceil(max(1.0, $durationHours / 24)) * $defaults['per_day']
                : ceil(max(1.0, $durationHours)) * $defaults['per_hour'];
        }
        $waitingFare = ($waitingMinutes / 60) * $defaults['waiting_per_hour'];

        $extraKmFare = 0.0;
        if ($distanceKm > $defaults['extra_km_threshold']) {
            $extraKmFare = ($distanceKm - $defaults['extra_km_threshold']) * $defaults['extra_km_charge'];
        }

        $subtotal = $baseFare + $distanceFare + $rentalFare + $waitingFare + $extraKmFare;

        $isNight = self::isWithinWindow($pickupDatetime, (string) ($defaults['night_start'] ?? ''), (string) ($defaults['night_end'] ?? ''));
        $isPeak = self::isWithinWindow($pickupDatetime, (string) ($defaults['peak_start'] ?? ''), (string) ($defaults['peak_end'] ?? ''));
        $nightCharge = $isNight ? ($subtotal * ($defaults['night_charge_percent'] / 100)) : 0.0;
        $peakCharge = $isPeak ? ($subtotal * ($defaults['peak_charge_percent'] / 100)) : 0.0;

        $couponCode = strtoupper(trim((string) ($input['coupon_code'] ?? '')));
        $discount = self::couponDiscount($couponCode, $subtotal + $nightCharge + $peakCharge);

        $total = max(0.0, $subtotal + $nightCharge + $peakCharge - $discount);

        return [
            'currency' => 'LKR',
            'vehicle_type' => $vehicleType,
            'trip_type' => $tripType,
            'distance_km' => round($distanceKm, 2),
            'duration_minutes' => $durationMinutes,
            'base_fare' => round($baseFare, 2),
            'distance_fare' => round($distanceFare, 2),
            'rental_fare' => round($rentalFare, 2),
            'waiting_fare' => round($waitingFare, 2),
            'extra_km_fare' => round($extraKmFare, 2),
            'night_charge' => round($nightCharge, 2),
            'peak_charge' => round($peakCharge, 2),
            'discount' => round($discount, 2),
            'total' => round($total, 2),
            'rule' => $rule,
        ];
    }

    private static function isWithinWindow(string $dateTime, string $startTime, string $endTime): bool
    {
        $dateTime = trim($dateTime);
        $startTime = trim($startTime);
        $endTime = trim($endTime);
        if ($dateTime === '' || $startTime === '' || $endTime === '') {
            return false;
        }
        $ts = strtotime($dateTime);
        if ($ts === false) {
            return false;
        }
        $time = date('H:i:s', $ts);
        if ($startTime <= $endTime) {
            return $time >= $startTime && $time <= $endTime;
        }

        return $time >= $startTime || $time <= $endTime;
    }

    private static function couponDiscount(string $couponCode, float $gross): float
    {
        if ($couponCode === '') {
            return 0.0;
        }
        $coupon = BookingCoupon::findByCode($couponCode);
        if (is_array($coupon)) {
            $min = max(0.0, (float) ($coupon['min_booking_amount'] ?? 0));
            if ($gross < $min) {
                return 0.0;
            }
            $type = (string) ($coupon['discount_type'] ?? 'percent');
            $value = max(0.0, (float) ($coupon['discount_value'] ?? 0));
            $discount = $type === 'flat' ? $value : ($gross * ($value / 100));
            $maxDiscount = (float) ($coupon['max_discount_amount'] ?? 0);
            if ($maxDiscount > 0) {
                $discount = min($discount, $maxDiscount);
            }

            return min($discount, $gross);
        }
        if ($couponCode === 'WELCOME10') {
            return min($gross * 0.1, 1500.0);
        }
        if ($couponCode === 'FLAT500') {
            return min(500.0, $gross);
        }

        return 0.0;
    }
}
