<?php

declare(strict_types=1);

namespace App\Models;

final class Post extends Model
{
    public static function latestPublished(int $limit): array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM posts WHERE status = "published" ORDER BY publish_date DESC, id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM posts WHERE slug = :slug AND status = "published" LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function countAll(): int
    {
        $stmt = self::pdo()->query('SELECT COUNT(*) AS c FROM posts');
        $row = $stmt->fetch();
        return (int) ($row['c'] ?? 0);
    }
}
