<?php

declare(strict_types=1);

namespace App\Models;

final class Destination extends Model
{
    public static function featured(int $limit): array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM destinations WHERE is_active = 1 ORDER BY is_featured DESC, sort_order ASC, id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function allActive(): array
    {
        $stmt = self::pdo()->query('SELECT * FROM destinations WHERE is_active = 1 ORDER BY sort_order ASC, name ASC');
        return $stmt->fetchAll();
    }

    public static function search(string $q, int $limit): array
    {
        $q = trim($q);
        if ($q === '') {
            $stmt = self::pdo()->prepare('SELECT * FROM destinations WHERE is_active = 1 ORDER BY is_featured DESC, sort_order ASC, name ASC LIMIT :lim');
            $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $stmt = self::pdo()->prepare('SELECT * FROM destinations WHERE is_active = 1 AND (name LIKE :q OR country LIKE :q) ORDER BY is_featured DESC, sort_order ASC, name ASC LIMIT :lim');
        $stmt->bindValue(':q', '%' . $q . '%', \PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM destinations WHERE slug = :slug AND is_active = 1 LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}
