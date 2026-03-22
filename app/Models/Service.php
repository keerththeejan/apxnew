<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Service extends Model
{
    /**
     * True when the `image_path` column exists.
     * If missing, attempts `ALTER TABLE` once (needs DB user with ALTER privilege) so local installs self-heal.
     */
    public static function hasImagePathColumn(): bool
    {
        try {
            $pdo = self::pdo();
            $st = $pdo->query("SHOW COLUMNS FROM `services` LIKE 'image_path'");
            if ($st !== false && $st->fetch() !== false) {
                return true;
            }
        } catch (\Throwable) {
            return false;
        }

        try {
            self::pdo()->exec('ALTER TABLE `services` ADD COLUMN `image_path` VARCHAR(500) NULL AFTER `icon`');

            return true;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (stripos($msg, 'Duplicate column') !== false || stripos($msg, '1060') !== false) {
                return true;
            }

            return false;
        }
    }

    public static function active(): array
    {
        $stmt = self::pdo()->query('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM services WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function countAll(): int
    {
        $stmt = self::pdo()->query('SELECT COUNT(*) AS c FROM services');
        $row = $stmt->fetch();
        return (int) ($row['c'] ?? 0);
    }

    /**
     * @return array{rows: list<array<string,mixed>>, total: int, page: int, perPage: int, pageCount: int}
     */
    public static function paginate(string $q, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $pdo = self::pdo();
        $where = '1=1';
        $params = [];
        $q = trim($q);
        if ($q !== '') {
            $where .= ' AND (title LIKE :q OR description LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        $cst = $pdo->prepare("SELECT COUNT(*) AS cnt FROM services WHERE {$where}");
        foreach ($params as $k => $v) {
            $cst->bindValue($k, $v);
        }
        $cst->execute();
        $total = (int) (($cst->fetch()['cnt'] ?? 0));
        $stmt = $pdo->prepare("SELECT * FROM services WHERE {$where} ORDER BY sort_order ASC, id ASC LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $pageCount = (int) max(1, (int) ceil($total / $perPage));

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pageCount' => $pageCount];
    }

    public static function create(array $data): int
    {
        $pdo = self::pdo();
        if (self::hasImagePathColumn()) {
            $stmt = $pdo->prepare('INSERT INTO services (icon, image_path, title, description, link, sort_order, is_active) VALUES (:icon,:img,:title,:desc,:link,:sort,:active)');
            $stmt->execute([
                ':icon' => (string) ($data['icon'] ?? ''),
                ':img' => (string) ($data['image_path'] ?? ''),
                ':title' => (string) ($data['title'] ?? ''),
                ':desc' => (string) ($data['description'] ?? ''),
                ':link' => (string) ($data['link'] ?? ''),
                ':sort' => (int) ($data['sort_order'] ?? 0),
                ':active' => (int) ($data['is_active'] ?? 1),
            ]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO services (icon, title, description, link, sort_order, is_active) VALUES (:icon,:title,:desc,:link,:sort,:active)');
            $stmt->execute([
                ':icon' => (string) ($data['icon'] ?? ''),
                ':title' => (string) ($data['title'] ?? ''),
                ':desc' => (string) ($data['description'] ?? ''),
                ':link' => (string) ($data['link'] ?? ''),
                ':sort' => (int) ($data['sort_order'] ?? 0),
                ':active' => (int) ($data['is_active'] ?? 1),
            ]);
        }

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        if (self::hasImagePathColumn()) {
            $stmt = self::pdo()->prepare('UPDATE services SET icon=:icon, image_path=:img, title=:title, description=:desc, link=:link, sort_order=:sort, is_active=:active WHERE id=:id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':icon', (string) ($data['icon'] ?? ''));
            $stmt->bindValue(':img', (string) ($data['image_path'] ?? ''));
            $stmt->bindValue(':title', (string) ($data['title'] ?? ''));
            $stmt->bindValue(':desc', (string) ($data['description'] ?? ''));
            $stmt->bindValue(':link', (string) ($data['link'] ?? ''));
            $stmt->bindValue(':sort', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':active', (int) ($data['is_active'] ?? 1), PDO::PARAM_INT);

            return $stmt->execute();
        }

        $stmt = self::pdo()->prepare('UPDATE services SET icon=:icon, title=:title, description=:desc, link=:link, sort_order=:sort, is_active=:active WHERE id=:id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':icon', (string) ($data['icon'] ?? ''));
        $stmt->bindValue(':title', (string) ($data['title'] ?? ''));
        $stmt->bindValue(':desc', (string) ($data['description'] ?? ''));
        $stmt->bindValue(':link', (string) ($data['link'] ?? ''));
        $stmt->bindValue(':sort', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':active', (int) ($data['is_active'] ?? 1), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM services WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
