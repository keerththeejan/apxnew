<?php

declare(strict_types=1);

namespace App\Models;

final class Testimonial extends Model
{
    public static function latest(int $limit): array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
