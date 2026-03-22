<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class User extends Model
{
    public static function countAll(): int
    {
        $stmt = self::pdo()->query('SELECT COUNT(*) AS c FROM users');
        $row = $stmt->fetch();
        return (int) ($row['c'] ?? 0);
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
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
            $where .= ' AND (name LIKE :q OR email LIKE :q OR phone LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        $cst = $pdo->prepare("SELECT COUNT(*) AS cnt FROM users WHERE {$where}");
        foreach ($params as $k => $v) {
            $cst->bindValue($k, $v);
        }
        $cst->execute();
        $total = (int) (($cst->fetch()['cnt'] ?? 0));
        $stmt = $pdo->prepare("SELECT * FROM users WHERE {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset");
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

    public static function setActive(int $id, int $active): bool
    {
        $stmt = self::pdo()->prepare('UPDATE users SET is_active = :a WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':a', $active, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function setRole(int $id, string $role): bool
    {
        $stmt = self::pdo()->prepare('UPDATE users SET role = :r WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':r', $role);
        return $stmt->execute();
    }
}
