<?php

declare(strict_types=1);

namespace App\Models;

final class Admin extends Model
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM admins WHERE email = :email AND is_active = 1 LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function updatePasswordHash(int $id, string $passwordHash): bool
    {
        $stmt = self::pdo()->prepare('UPDATE admins SET password_hash = :h, updated_at = NOW() WHERE id = :id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':h', $passwordHash);

        return $stmt->execute();
    }
}
