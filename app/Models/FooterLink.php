<?php

declare(strict_types=1);

namespace App\Models;

final class FooterLink extends Model
{
    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public static function groupedActive(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM footer_links WHERE is_active = 1 ORDER BY group_name ASC, sort_order ASC, id ASC');
            $rows = $stmt->fetchAll() ?: [];
            $out = [];
            foreach ($rows as $r) {
                $g = (string) ($r['group_name'] ?? '');
                $out[$g][] = $r;
            }
            return $out;
        }, []);
    }

    /** @return list<array<string, mixed>> */
    public static function allOrdered(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM footer_links ORDER BY group_name ASC, sort_order ASC, id ASC');
            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function create(array $data): int
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare('INSERT INTO footer_links (group_name, label, url, sort_order, is_active) VALUES (:g,:label,:url,:sort,:active)');
        $stmt->execute([
            ':g' => (string) ($data['group_name'] ?? ''),
            ':label' => (string) ($data['label'] ?? ''),
            ':url' => (string) ($data['url'] ?? ''),
            ':sort' => (int) ($data['sort_order'] ?? 0),
            ':active' => (int) ($data['is_active'] ?? 1),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = self::pdo()->prepare('UPDATE footer_links SET group_name=:g, label=:label, url=:url, sort_order=:sort, is_active=:active WHERE id=:id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':g', (string) ($data['group_name'] ?? ''));
        $stmt->bindValue(':label', (string) ($data['label'] ?? ''));
        $stmt->bindValue(':url', (string) ($data['url'] ?? ''));
        $stmt->bindValue(':sort', (int) ($data['sort_order'] ?? 0), \PDO::PARAM_INT);
        $stmt->bindValue(':active', (int) ($data['is_active'] ?? 1), \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM footer_links WHERE id = :id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
