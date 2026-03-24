<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleAvailability;

final class VehicleAvailabilityService
{
    /**
     * @return array{vehicles:list<array<string,mixed>>,drivers:list<array<string,mixed>>}
     */
    public static function availableOptions(string $vehicleType, string $startAt, string $endAt, ?int $excludeBookingId = null): array
    {
        $vehicles = Vehicle::availableByType($vehicleType);
        $eligible = [];
        foreach ($vehicles as $v) {
            $vid = (int) ($v['id'] ?? 0);
            if ($vid < 1) {
                continue;
            }
            if (!VehicleAvailability::hasConflict($vid, $startAt, $endAt, $excludeBookingId)) {
                $eligible[] = $v;
            }
        }

        return [
            'vehicles' => $eligible,
            'drivers' => Driver::available(),
        ];
    }
}
