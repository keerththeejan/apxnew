<?php

declare(strict_types=1);

namespace App\Models;

final class Branch extends Model
{
    /** @return list<array<string,mixed>> */
    public static function active(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM branches WHERE is_active = 1 ORDER BY name ASC');

            return $stmt->fetchAll() ?: [];
        }, []);
    }
}
