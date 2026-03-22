<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class AdminPasswordReset extends Model
{
    public static function createForAdmin(int $adminId, string $plainToken, int $ttlSeconds = 3600): void
    {
        $pdo = self::pdo();
        $del = $pdo->prepare('DELETE FROM admin_password_resets WHERE admin_id = :id');
        $del->execute([':id' => $adminId]);

        $hash = hash('sha256', $plainToken);
        $expires = date('Y-m-d H:i:s', time() + max(300, min(86400, $ttlSeconds)));
        $stmt = $pdo->prepare('INSERT INTO admin_password_resets (admin_id, token_hash, expires_at) VALUES (:id, :h, :ex)');
        $stmt->execute([':id' => $adminId, ':h' => $hash, ':ex' => $expires]);
    }

    /**
     * @return array{id:int,email:string}|null
     */
    public static function consume(string $plainToken): ?array
    {
        $hash = hash('sha256', $plainToken);
        $pdo = self::pdo();
        $stmt = $pdo->prepare(
            'SELECT r.id AS rid, r.admin_id, a.email FROM admin_password_resets r INNER JOIN admins a ON a.id = r.admin_id
             WHERE r.token_hash = :h AND r.used_at IS NULL AND r.expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([':h' => $hash]);
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }
        $rid = (int) ($row['rid'] ?? 0);
        $upd = $pdo->prepare('UPDATE admin_password_resets SET used_at = NOW() WHERE id = :id');
        $upd->execute([':id' => $rid]);

        return ['id' => (int) ($row['admin_id'] ?? 0), 'email' => (string) ($row['email'] ?? '')];
    }
}
