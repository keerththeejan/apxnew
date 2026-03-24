<?php

declare(strict_types=1);

namespace App\Models;

final class VehicleAvailability extends Model
{
    public static function schemaReady(): bool
    {
        return self::safe(function (): bool {
            self::pdo()->query('SELECT 1 FROM vehicle_availability LIMIT 1');

            return true;
        }, false);
    }

    /** @return list<array<string,mixed>> */
    public static function byVehicle(int $vehicleId): array
    {
        if ($vehicleId < 1) {
            return [];
        }
        return self::safe(function () use ($vehicleId): array {
            $stmt = self::pdo()->prepare('SELECT * FROM vehicle_availability WHERE vehicle_id = :id ORDER BY start_at DESC, id DESC');
            $stmt->execute([':id' => $vehicleId]);

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function hasConflict(int $vehicleId, string $startAt, string $endAt, ?int $excludeBookingId = null): bool
    {
        if ($vehicleId < 1 || trim($startAt) === '' || trim($endAt) === '') {
            return false;
        }
        return self::safe(function () use ($vehicleId, $startAt, $endAt, $excludeBookingId): bool {
            $sql = 'SELECT COUNT(*) AS c
                    FROM vehicle_availability
                    WHERE vehicle_id = :vehicle_id
                      AND start_at < :end_at
                      AND end_at > :start_at';
            $params = [
                ':vehicle_id' => $vehicleId,
                ':start_at' => $startAt,
                ':end_at' => $endAt,
            ];
            if ($excludeBookingId !== null && $excludeBookingId > 0) {
                $sql .= ' AND (booking_id IS NULL OR booking_id <> :booking_id)';
                $params[':booking_id'] = $excludeBookingId;
            }
            $stmt = self::pdo()->prepare($sql);
            $stmt->execute($params);

            return (int) (($stmt->fetch()['c'] ?? 0)) > 0;
        }, false);
    }

    public static function reserve(int $vehicleId, ?int $bookingId, string $startAt, string $endAt, string $status = 'reserved', ?string $notes = null): int
    {
        $stmt = self::pdo()->prepare(
            'INSERT INTO vehicle_availability (vehicle_id, booking_id, start_at, end_at, availability_status, notes)
             VALUES (:vehicle_id,:booking_id,:start_at,:end_at,:availability_status,:notes)'
        );
        $stmt->execute([
            ':vehicle_id' => $vehicleId,
            ':booking_id' => $bookingId,
            ':start_at' => $startAt,
            ':end_at' => $endAt,
            ':availability_status' => $status,
            ':notes' => $notes,
        ]);

        return (int) self::pdo()->lastInsertId();
    }

    public static function releaseByBooking(int $bookingId): bool
    {
        if ($bookingId < 1) {
            return false;
        }
        $stmt = self::pdo()->prepare('DELETE FROM vehicle_availability WHERE booking_id = :booking_id');

        return $stmt->execute([':booking_id' => $bookingId]);
    }

    /** @return array{rows:list<array<string,mixed>>, total:int, page:int, perPage:int, pageCount:int} */
    public static function paginate(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        return self::safe(function () use ($page, $perPage, $offset): array {
            $pdo = self::pdo();
            $total = (int) (($pdo->query('SELECT COUNT(*) AS cnt FROM vehicle_availability')->fetch()['cnt'] ?? 0));
            $stmt = $pdo->prepare('SELECT a.*, v.name AS vehicle_name, b.booking_ref
                FROM vehicle_availability a
                LEFT JOIN vehicles v ON v.id = a.vehicle_id
                LEFT JOIN vehicle_bookings b ON b.id = a.booking_id
                ORDER BY a.start_at DESC, a.id DESC
                LIMIT :limit OFFSET :offset');
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
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

    public static function delete(int $id): bool
    {
        $stmt = self::pdo()->prepare('DELETE FROM vehicle_availability WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }
}
