<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class VehicleBooking extends Model
{
    public static function schemaReady(): bool
    {
        return self::safe(function (): bool {
            self::pdo()->query('SELECT 1 FROM vehicle_bookings LIMIT 1');

            return true;
        }, false);
    }

    public static function countAll(): int
    {
        return (int) self::safe(function (): int {
            $stmt = self::pdo()->query('SELECT COUNT(*) AS c FROM vehicle_bookings');

            return (int) (($stmt->fetch()['c'] ?? 0));
        }, 0);
    }

    public static function revenueForDate(string $date): float
    {
        $date = trim($date);
        if ($date === '') {
            return 0.0;
        }
        return (float) self::safe(function () use ($date): float {
            $stmt = self::pdo()->prepare("SELECT COALESCE(SUM(estimated_total),0) AS total FROM vehicle_bookings WHERE DATE(pickup_datetime) = :d AND status IN ('completed','on_trip','assigned','confirmed')");
            $stmt->execute([':d' => $date]);

            return (float) (($stmt->fetch()['total'] ?? 0));
        }, 0.0);
    }

    public static function revenueForMonth(string $ym): float
    {
        $ym = trim($ym);
        if ($ym === '') {
            return 0.0;
        }
        return (float) self::safe(function () use ($ym): float {
            $stmt = self::pdo()->prepare("SELECT COALESCE(SUM(estimated_total),0) AS total FROM vehicle_bookings WHERE DATE_FORMAT(pickup_datetime, '%Y-%m') = :ym AND status IN ('completed','on_trip','assigned','confirmed')");
            $stmt->execute([':ym' => $ym]);

            return (float) (($stmt->fetch()['total'] ?? 0));
        }, 0.0);
    }

    /** @return list<array<string,mixed>> */
    public static function driverPerformance(): array
    {
        return self::safe(function (): array {
            $sql = "SELECT d.id, d.name, COUNT(b.id) AS trips, COALESCE(SUM(b.estimated_total),0) AS revenue
                    FROM drivers d
                    LEFT JOIN vehicle_bookings b ON b.driver_id = d.id AND b.status IN ('completed','on_trip','assigned')
                    GROUP BY d.id, d.name
                    ORDER BY trips DESC, revenue DESC, d.name ASC";
            $stmt = self::pdo()->query($sql);

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    /** @return list<array<string,mixed>> */
    public static function vehicleUtilization(): array
    {
        return self::safe(function (): array {
            $sql = "SELECT v.id, v.name, COUNT(b.id) AS trips
                    FROM vehicles v
                    LEFT JOIN vehicle_bookings b ON b.vehicle_id = v.id AND b.status IN ('completed','on_trip','assigned')
                    GROUP BY v.id, v.name
                    ORDER BY trips DESC, v.name ASC";
            $stmt = self::pdo()->query($sql);

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    /** @return array<string,int> */
    public static function countsByStatus(): array
    {
        return self::safe(function (): array {
            $rows = self::pdo()->query('SELECT status, COUNT(*) AS c FROM vehicle_bookings GROUP BY status')->fetchAll() ?: [];
            $out = [];
            foreach ($rows as $r) {
                $out[(string) ($r['status'] ?? '')] = (int) ($r['c'] ?? 0);
            }

            return $out;
        }, []);
    }

    public static function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        return self::safe(function () use ($id): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM vehicle_bookings WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            return $row === false ? null : $row;
        }, null);
    }

    /** @return array{rows:list<array<string,mixed>>, total:int, page:int, perPage:int, pageCount:int} */
    public static function paginate(array $filters, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        return self::safe(function () use ($filters, $page, $perPage, $offset): array {
            $where = '1=1';
            $params = [];

            $status = trim((string) ($filters['status'] ?? ''));
            $vehicleType = trim((string) ($filters['vehicle_type'] ?? ''));
            $from = trim((string) ($filters['from'] ?? ''));
            $to = trim((string) ($filters['to'] ?? ''));
            $q = trim((string) ($filters['q'] ?? ''));
            $driverId = (int) ($filters['driver_id'] ?? 0);

            if ($status !== '') {
                $where .= ' AND status = :status';
                $params[':status'] = $status;
            }
            if ($vehicleType !== '') {
                $where .= ' AND vehicle_type = :vehicle_type';
                $params[':vehicle_type'] = $vehicleType;
            }
            if ($from !== '') {
                $where .= ' AND pickup_datetime >= :from_date';
                $params[':from_date'] = $from . ' 00:00:00';
            }
            if ($to !== '') {
                $where .= ' AND pickup_datetime <= :to_date';
                $params[':to_date'] = $to . ' 23:59:59';
            }
            if ($q !== '') {
                $where .= ' AND (booking_ref LIKE :q OR customer_name LIKE :q OR customer_phone LIKE :q)';
                $params[':q'] = '%' . $q . '%';
            }
            if ($driverId > 0) {
                $where .= ' AND driver_id = :driver_id';
                $params[':driver_id'] = $driverId;
            }

            $pdo = self::pdo();
            $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM vehicle_bookings WHERE {$where}");
            $countStmt->execute($params);
            $total = (int) (($countStmt->fetch()['cnt'] ?? 0));

            $stmt = $pdo->prepare("SELECT * FROM vehicle_bookings WHERE {$where} ORDER BY pickup_datetime DESC, id DESC LIMIT :limit OFFSET :offset");
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'rows' => $stmt->fetchAll() ?: [],
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'pageCount' => (int) max(1, (int) ceil($total / $perPage)),
            ];
        }, ['rows' => [], 'total' => 0, 'page' => $page, 'perPage' => $perPage, 'pageCount' => 1]);
    }

    /** @param array<string,mixed> $data */
    public static function create(array $data): int
    {
        $stmt = self::pdo()->prepare(
            'INSERT INTO vehicle_bookings (
                booking_ref, branch_id, vehicle_id, driver_id, coupon_id, booking_mode, trip_type, rental_unit, vehicle_type,
                pickup_location, pickup_lat, pickup_lng, drop_location, drop_lat, drop_lng, pickup_datetime, return_datetime,
                passenger_count, luggage_count, customer_name, customer_phone, customer_email, customer_notes,
                distance_km, duration_minutes, estimated_total, currency_code, pricing_breakdown_json, status, otp_code, otp_verified_at
             ) VALUES (
                :booking_ref, :branch_id, :vehicle_id, :driver_id, :coupon_id, :booking_mode, :trip_type, :rental_unit, :vehicle_type,
                :pickup_location, :pickup_lat, :pickup_lng, :drop_location, :drop_lat, :drop_lng, :pickup_datetime, :return_datetime,
                :passenger_count, :luggage_count, :customer_name, :customer_phone, :customer_email, :customer_notes,
                :distance_km, :duration_minutes, :estimated_total, :currency_code, :pricing_breakdown_json, :status, :otp_code, :otp_verified_at
             )'
        );

        $stmt->execute([
            ':booking_ref' => trim((string) ($data['booking_ref'] ?? self::generateRef())),
            ':branch_id' => self::nullInt($data['branch_id'] ?? null),
            ':vehicle_id' => self::nullInt($data['vehicle_id'] ?? null),
            ':driver_id' => self::nullInt($data['driver_id'] ?? null),
            ':coupon_id' => self::nullInt($data['coupon_id'] ?? null),
            ':booking_mode' => trim((string) ($data['booking_mode'] ?? 'ride')),
            ':trip_type' => trim((string) ($data['trip_type'] ?? 'one_way')),
            ':rental_unit' => self::nullableString($data['rental_unit'] ?? null),
            ':vehicle_type' => trim((string) ($data['vehicle_type'] ?? 'car')),
            ':pickup_location' => trim((string) ($data['pickup_location'] ?? '')),
            ':pickup_lat' => self::nullableDecimal($data['pickup_lat'] ?? null),
            ':pickup_lng' => self::nullableDecimal($data['pickup_lng'] ?? null),
            ':drop_location' => self::nullableString($data['drop_location'] ?? null),
            ':drop_lat' => self::nullableDecimal($data['drop_lat'] ?? null),
            ':drop_lng' => self::nullableDecimal($data['drop_lng'] ?? null),
            ':pickup_datetime' => trim((string) ($data['pickup_datetime'] ?? '')),
            ':return_datetime' => self::nullableString($data['return_datetime'] ?? null),
            ':passenger_count' => (int) ($data['passenger_count'] ?? 1),
            ':luggage_count' => (int) ($data['luggage_count'] ?? 0),
            ':customer_name' => trim((string) ($data['customer_name'] ?? '')),
            ':customer_phone' => trim((string) ($data['customer_phone'] ?? '')),
            ':customer_email' => self::nullableString($data['customer_email'] ?? null),
            ':customer_notes' => self::nullableString($data['customer_notes'] ?? null),
            ':distance_km' => self::decimalString($data['distance_km'] ?? 0),
            ':duration_minutes' => (int) ($data['duration_minutes'] ?? 0),
            ':estimated_total' => self::decimalString($data['estimated_total'] ?? 0),
            ':currency_code' => trim((string) ($data['currency_code'] ?? 'LKR')),
            ':pricing_breakdown_json' => is_string($data['pricing_breakdown_json'] ?? null) ? $data['pricing_breakdown_json'] : json_encode($data['pricing_breakdown_json'] ?? [], JSON_UNESCAPED_UNICODE),
            ':status' => trim((string) ($data['status'] ?? 'pending')),
            ':otp_code' => self::nullableString($data['otp_code'] ?? null),
            ':otp_verified_at' => self::nullableString($data['otp_verified_at'] ?? null),
        ]);
        $id = (int) self::pdo()->lastInsertId();

        return $id;
    }

    public static function createWithInitialLog(array $data, ?int $adminId = null, ?string $notes = null): int
    {
        $id = self::create($data);
        $status = trim((string) ($data['status'] ?? 'pending'));
        BookingStatusLog::create($id, null, $status, $adminId, $notes);

        return $id;
    }

    /** @param array<string,mixed> $data */
    public static function updateById(int $id, array $data): bool
    {
        if ($id < 1) {
            return false;
        }
        $stmt = self::pdo()->prepare(
            'UPDATE vehicle_bookings SET
                branch_id=:branch_id, vehicle_id=:vehicle_id, driver_id=:driver_id, coupon_id=:coupon_id, booking_mode=:booking_mode, trip_type=:trip_type, rental_unit=:rental_unit, vehicle_type=:vehicle_type,
                pickup_location=:pickup_location, pickup_lat=:pickup_lat, pickup_lng=:pickup_lng, drop_location=:drop_location, drop_lat=:drop_lat, drop_lng=:drop_lng,
                pickup_datetime=:pickup_datetime, return_datetime=:return_datetime, passenger_count=:passenger_count, luggage_count=:luggage_count,
                customer_name=:customer_name, customer_phone=:customer_phone, customer_email=:customer_email, customer_notes=:customer_notes,
                distance_km=:distance_km, duration_minutes=:duration_minutes, estimated_total=:estimated_total, currency_code=:currency_code,
                pricing_breakdown_json=:pricing_breakdown_json, status=:status, otp_code=:otp_code, otp_verified_at=:otp_verified_at, updated_at=NOW()
             WHERE id=:id'
        );

        return $stmt->execute([
            ':id' => $id,
            ':branch_id' => self::nullInt($data['branch_id'] ?? null),
            ':vehicle_id' => self::nullInt($data['vehicle_id'] ?? null),
            ':driver_id' => self::nullInt($data['driver_id'] ?? null),
            ':coupon_id' => self::nullInt($data['coupon_id'] ?? null),
            ':booking_mode' => trim((string) ($data['booking_mode'] ?? 'ride')),
            ':trip_type' => trim((string) ($data['trip_type'] ?? 'one_way')),
            ':rental_unit' => self::nullableString($data['rental_unit'] ?? null),
            ':vehicle_type' => trim((string) ($data['vehicle_type'] ?? 'car')),
            ':pickup_location' => trim((string) ($data['pickup_location'] ?? '')),
            ':pickup_lat' => self::nullableDecimal($data['pickup_lat'] ?? null),
            ':pickup_lng' => self::nullableDecimal($data['pickup_lng'] ?? null),
            ':drop_location' => self::nullableString($data['drop_location'] ?? null),
            ':drop_lat' => self::nullableDecimal($data['drop_lat'] ?? null),
            ':drop_lng' => self::nullableDecimal($data['drop_lng'] ?? null),
            ':pickup_datetime' => trim((string) ($data['pickup_datetime'] ?? '')),
            ':return_datetime' => self::nullableString($data['return_datetime'] ?? null),
            ':passenger_count' => (int) ($data['passenger_count'] ?? 1),
            ':luggage_count' => (int) ($data['luggage_count'] ?? 0),
            ':customer_name' => trim((string) ($data['customer_name'] ?? '')),
            ':customer_phone' => trim((string) ($data['customer_phone'] ?? '')),
            ':customer_email' => self::nullableString($data['customer_email'] ?? null),
            ':customer_notes' => self::nullableString($data['customer_notes'] ?? null),
            ':distance_km' => self::decimalString($data['distance_km'] ?? 0),
            ':duration_minutes' => (int) ($data['duration_minutes'] ?? 0),
            ':estimated_total' => self::decimalString($data['estimated_total'] ?? 0),
            ':currency_code' => trim((string) ($data['currency_code'] ?? 'LKR')),
            ':pricing_breakdown_json' => is_string($data['pricing_breakdown_json'] ?? null) ? $data['pricing_breakdown_json'] : json_encode($data['pricing_breakdown_json'] ?? [], JSON_UNESCAPED_UNICODE),
            ':status' => trim((string) ($data['status'] ?? 'pending')),
            ':otp_code' => self::nullableString($data['otp_code'] ?? null),
            ':otp_verified_at' => self::nullableString($data['otp_verified_at'] ?? null),
        ]);
    }

    public static function assignVehicleDriver(int $id, ?int $vehicleId, ?int $driverId, ?int $adminId = null): bool
    {
        $current = self::findById($id);
        if ($current === null) {
            return false;
        }
        $newStatus = 'assigned';
        $stmt = self::pdo()->prepare('UPDATE vehicle_bookings SET vehicle_id=:vehicle_id, driver_id=:driver_id, status=:status, updated_at=NOW() WHERE id=:id');
        $ok = $stmt->execute([
            ':id' => $id,
            ':vehicle_id' => self::nullInt($vehicleId),
            ':driver_id' => self::nullInt($driverId),
            ':status' => $newStatus,
        ]);
        if ($ok) {
            BookingStatusLog::create($id, (string) ($current['status'] ?? 'pending'), $newStatus, $adminId, 'Driver/vehicle assigned');
        }

        return $ok;
    }

    public static function updateStatus(int $id, string $newStatus, ?int $adminId = null, ?string $notes = null): bool
    {
        $current = self::findById($id);
        if ($current === null) {
            return false;
        }
        $old = (string) ($current['status'] ?? 'pending');
        if ($old === $newStatus) {
            return true;
        }

        $stmt = self::pdo()->prepare('UPDATE vehicle_bookings SET status=:status, updated_at=NOW() WHERE id=:id');
        $ok = $stmt->execute([
            ':id' => $id,
            ':status' => $newStatus,
        ]);
        if ($ok) {
            BookingStatusLog::create($id, $old, $newStatus, $adminId, $notes);
        }

        return $ok;
    }

    public static function delete(int $id): bool
    {
        $stmt = self::pdo()->prepare('DELETE FROM vehicle_bookings WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    public static function generateRef(): string
    {
        return 'VB' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    private static function nullInt(mixed $value): ?int
    {
        $x = (int) $value;

        return $x > 0 ? $x : null;
    }

    private static function nullableString(mixed $value): ?string
    {
        $x = trim((string) $value);

        return $x !== '' ? $x : null;
    }

    private static function nullableDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::decimalString($value);
    }

    private static function decimalString(mixed $value): string
    {
        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }
        $f = (float) $value;

        return number_format($f, 2, '.', '');
    }
}
