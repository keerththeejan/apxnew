<?php

declare(strict_types=1);

namespace App\Models;

final class BookingCoupon extends Model
{
    /** @return list<array<string,mixed>> */
    public static function active(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM booking_coupons WHERE is_active = 1 ORDER BY id DESC');

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function findByCode(string $code): ?array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }
        return self::safe(function () use ($code): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM booking_coupons WHERE code = :code AND is_active = 1 LIMIT 1');
            $stmt->execute([':code' => $code]);
            $row = $stmt->fetch();

            return $row === false ? null : $row;
        }, null);
    }
}
