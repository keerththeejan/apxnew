<?php

declare(strict_types=1);

namespace App\Models;

final class AdminLoginAttempt extends Model
{
    public static function record(string $email, string $ip): void
    {
        $stmt = self::pdo()->prepare('INSERT INTO admin_login_attempts (email, ip) VALUES (:e, :ip)');
        $stmt->execute([
            ':e' => strtolower(trim($email)),
            ':ip' => $ip,
        ]);
    }

    public static function countRecent(string $email, string $ip, int $windowMinutes): int
    {
        $windowMinutes = max(1, min(1440, $windowMinutes));
        $since = date('Y-m-d H:i:s', time() - $windowMinutes * 60);
        $stmt = self::pdo()->prepare('SELECT COUNT(*) AS c FROM admin_login_attempts WHERE email = :e AND ip = :ip AND attempted_at >= :since');
        $stmt->execute([
            ':e' => strtolower(trim($email)),
            ':ip' => $ip,
            ':since' => $since,
        ]);
        $row = $stmt->fetch();

        return (int) ($row['c'] ?? 0);
    }

    public static function pruneOld(): void
    {
        self::pdo()->exec('DELETE FROM admin_login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)');
    }
}
