<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class AdminNotification extends Model
{
    public static function unreadCount(): int
    {
        try {
            $stmt = self::pdo()->query('SELECT COUNT(*) AS c FROM admin_notifications WHERE is_read = 0');
            $row = $stmt ? $stmt->fetch() : false;
            return (int) (($row !== false ? $row['c'] : 0));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function latest(int $limit): array
    {
        try {
            $stmt = self::pdo()->prepare('SELECT * FROM admin_notifications ORDER BY id DESC LIMIT :lim');
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function create(string $message, string $type = 'info', ?int $adminId = null): void
    {
        try {
            $stmt = self::pdo()->prepare(
                'INSERT INTO admin_notifications (admin_id, message, type, is_read) VALUES (:aid, :msg, :typ, 0)'
            );
            $stmt->bindValue(':aid', $adminId, $adminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->execute([':msg' => $message, ':typ' => $type]);
        } catch (\Throwable $e) {
        }
    }

    public static function markAllRead(): void
    {
        try {
            self::pdo()->exec('UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0');
        } catch (\Throwable $e) {
        }
    }
}
