<?php

declare(strict_types=1);

namespace App\Models;

final class FooterGallery extends Model
{
    /** @return list<array<string, mixed>> */
    public static function activeOrdered(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM footer_gallery WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
            return $stmt->fetchAll() ?: [];
        }, []);
    }

    /** @return list<array<string, mixed>> */
    public static function allOrdered(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM footer_gallery ORDER BY sort_order ASC, id ASC');
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
            $stmt = self::pdo()->prepare('SELECT * FROM footer_gallery WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            return $row !== false ? $row : null;
        }, null);
    }

    public static function create(array $data): int
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare('INSERT INTO footer_gallery (image_path, alt_text, sort_order, is_active) VALUES (:p,:alt,:sort,:active)');
        $stmt->execute([
            ':p' => (string) ($data['image_path'] ?? ''),
            ':alt' => (string) ($data['alt_text'] ?? ''),
            ':sort' => (int) ($data['sort_order'] ?? 0),
            ':active' => (int) ($data['is_active'] ?? 1),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = self::pdo()->prepare('UPDATE footer_gallery SET image_path=:p, alt_text=:alt, sort_order=:sort, is_active=:active WHERE id=:id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':p', (string) ($data['image_path'] ?? ''));
        $stmt->bindValue(':alt', (string) ($data['alt_text'] ?? ''));
        $stmt->bindValue(':sort', (int) ($data['sort_order'] ?? 0), \PDO::PARAM_INT);
        $stmt->bindValue(':active', (int) ($data['is_active'] ?? 1), \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM footer_gallery WHERE id = :id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
