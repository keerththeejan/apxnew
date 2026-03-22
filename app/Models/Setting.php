<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\SiteConfig;

final class Setting extends Model
{
    public static function allKeyed(): array
    {
        $stmt = self::pdo()->query('SELECT `key`, `value` FROM settings');
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[(string) $r['key']] = (string) ($r['value'] ?? '');
        }
        return $out;
    }

    public static function set(string $key, string $value): void
    {
        $stmt = self::pdo()->prepare('INSERT INTO settings (`key`, `value`) VALUES (:k, :v) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
        $stmt->execute([':k' => $key, ':v' => $value]);
        SiteConfig::forget();
    }
}
