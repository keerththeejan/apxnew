<?php

declare(strict_types=1);

namespace App\Models;

final class BookingStatusLog extends Model
{
    public static function schemaReady(): bool
    {
        return self::safe(function (): bool {
            self::pdo()->query('SELECT 1 FROM booking_status_logs LIMIT 1');

            return true;
        }, false);
    }

    public static function create(int $bookingId, ?string $oldStatus, string $newStatus, ?int $adminId = null, ?string $notes = null): int
    {
        $stmt = self::pdo()->prepare(
            'INSERT INTO booking_status_logs (booking_id, old_status, new_status, changed_by_admin_id, notes) VALUES (:booking_id,:old_status,:new_status,:admin_id,:notes)'
        );
        $stmt->execute([
            ':booking_id' => $bookingId,
            ':old_status' => $oldStatus,
            ':new_status' => $newStatus,
            ':admin_id' => $adminId,
            ':notes' => $notes,
        ]);

        return (int) self::pdo()->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public static function byBooking(int $bookingId): array
    {
        if ($bookingId < 1) {
            return [];
        }

        return self::safe(function () use ($bookingId): array {
            $stmt = self::pdo()->prepare('SELECT * FROM booking_status_logs WHERE booking_id = :id ORDER BY id DESC');
            $stmt->execute([':id' => $bookingId]);

            return $stmt->fetchAll() ?: [];
        }, []);
    }
}
