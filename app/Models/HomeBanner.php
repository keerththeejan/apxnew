<?php

declare(strict_types=1);

namespace App\Models;

final class HomeBanner extends Model
{
    /** @return list<array<string, mixed>> */
    public static function activeOrdered(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query(
                'SELECT * FROM home_banners WHERE is_active = 1 ORDER BY order_index ASC, id ASC'
            );

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    /** @return list<array<string, mixed>> */
    public static function allOrdered(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM home_banners ORDER BY order_index ASC, id ASC');

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    /** @return array<string, mixed>|null */
    public static function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        return self::safe(function () use ($id): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM home_banners WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            return $row !== false ? $row : null;
        }, null);
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data): int
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO home_banners (title, subtitle, image_path, show_image, button1_text, button1_link, button2_text, button2_link, order_index, is_active)
             VALUES (:title,:subtitle,:img,:showimg,:b1t,:b1u,:b2t,:b2u,:oi,:active)'
        );
        $stmt->execute([
            ':title' => (string) ($data['title'] ?? ''),
            ':subtitle' => self::nullStr($data['subtitle'] ?? null),
            ':img' => self::nullStr($data['image_path'] ?? null),
            ':showimg' => (int) ($data['show_image'] ?? 1) === 1 ? 1 : 0,
            ':b1t' => (string) ($data['button1_text'] ?? ''),
            ':b1u' => (string) ($data['button1_link'] ?? ''),
            ':b2t' => (string) ($data['button2_text'] ?? ''),
            ':b2u' => (string) ($data['button2_link'] ?? ''),
            ':oi' => (int) ($data['order_index'] ?? 0),
            ':active' => (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public static function update(int $id, array $data): void
    {
        if ($id < 1) {
            return;
        }
        $stmt = self::pdo()->prepare(
            'UPDATE home_banners SET title=:title, subtitle=:subtitle, image_path=:img, show_image=:showimg,
             button1_text=:b1t, button1_link=:b1u, button2_text=:b2t, button2_link=:b2u, order_index=:oi, is_active=:active
             WHERE id=:id'
        );
        $stmt->execute([
            ':id' => $id,
            ':title' => (string) ($data['title'] ?? ''),
            ':subtitle' => self::nullStr($data['subtitle'] ?? null),
            ':img' => self::nullStr($data['image_path'] ?? null),
            ':showimg' => (int) ($data['show_image'] ?? 1) === 1 ? 1 : 0,
            ':b1t' => (string) ($data['button1_text'] ?? ''),
            ':b1u' => (string) ($data['button1_link'] ?? ''),
            ':b2t' => (string) ($data['button2_text'] ?? ''),
            ':b2u' => (string) ($data['button2_link'] ?? ''),
            ':oi' => (int) ($data['order_index'] ?? 0),
            ':active' => (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
        ]);
    }

    public static function deleteById(int $id): void
    {
        if ($id < 1) {
            return;
        }
        $stmt = self::pdo()->prepare('DELETE FROM home_banners WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
    }

    private static function nullStr(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }
}
