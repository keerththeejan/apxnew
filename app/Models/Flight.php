<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Flight extends Model
{
    public static function deals(int $limit): array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM flights WHERE is_active = 1 ORDER BY is_deal DESC, id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function paginate(string $q, int $page, int $perPage, string $sort, string $dir): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $allowedSort = ['id', 'title', 'origin', 'destination', 'price_from', 'is_deal', 'is_active', 'updated_at', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'updated_at';
        }
        $dir = strtoupper($dir);
        if ($dir !== 'ASC' && $dir !== 'DESC') {
            $dir = 'DESC';
        }

        $pdo = self::pdo();
        $whereSql = '';
        $params = [];
        $q = trim($q);
        if ($q !== '') {
            $whereSql = 'WHERE title LIKE :q OR origin LIKE :q OR destination LIKE :q';
            $params[':q'] = '%' . $q . '%';
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM flights {$whereSql}");
        foreach ($params as $k => $v) {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();
        $total = (int) (($countStmt->fetch()['cnt'] ?? 0));

        $stmt = $pdo->prepare("SELECT id, title, summary, origin, destination, price_from, is_deal, is_active, updated_at, created_at FROM flights {$whereSql} ORDER BY `{$sort}` {$dir} LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        $pageCount = (int) max(1, (int) ceil($total / $perPage));

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pageCount' => $pageCount,
        ];
    }

    public static function create(array $data): int
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare('INSERT INTO flights (title, summary, origin, destination, price_from, is_deal, is_active) VALUES (:title, :summary, :origin, :destination, :price_from, :is_deal, :is_active)');
        $stmt->bindValue(':title', (string) ($data['title'] ?? ''));
        $stmt->bindValue(':summary', (string) ($data['summary'] ?? ''));
        $stmt->bindValue(':origin', (string) ($data['origin'] ?? ''));
        $stmt->bindValue(':destination', (string) ($data['destination'] ?? ''));
        $stmt->bindValue(':price_from', (string) ($data['price_from'] ?? ''));
        $stmt->bindValue(':is_deal', (int) ($data['is_deal'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':is_active', (int) ($data['is_active'] ?? 1), PDO::PARAM_INT);
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare('UPDATE flights SET title=:title, summary=:summary, origin=:origin, destination=:destination, price_from=:price_from, is_deal=:is_deal, is_active=:is_active WHERE id=:id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':title', (string) ($data['title'] ?? ''));
        $stmt->bindValue(':summary', (string) ($data['summary'] ?? ''));
        $stmt->bindValue(':origin', (string) ($data['origin'] ?? ''));
        $stmt->bindValue(':destination', (string) ($data['destination'] ?? ''));
        $stmt->bindValue(':price_from', (string) ($data['price_from'] ?? ''));
        $stmt->bindValue(':is_deal', (int) ($data['is_deal'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':is_active', (int) ($data['is_active'] ?? 1), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare('DELETE FROM flights WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
