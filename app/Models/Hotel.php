<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Hotel extends Model
{
    /** @return list<array<string,mixed>> */
    public static function featured(int $limit): array
    {
        $limit = max(1, min(100, $limit));

        return self::safe(function () use ($limit): array {
            $stmt = self::pdo()->prepare('SELECT * FROM hotels WHERE is_active = 1 ORDER BY is_featured DESC, id DESC LIMIT :lim');
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function findById(int $id): ?array
    {
        return self::safe(function () use ($id): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM hotels WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row === false ? null : $row;
        }, null);
    }

    /**
     * @return array{rows: list<array<string,mixed>>, total: int, page: int, perPage: int, pageCount: int}
     */
    public static function paginate(string $q, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $q = trim($q);

        return self::safe(function () use ($q, $page, $perPage, $offset): array {
            $pdo = self::pdo();
            $where = '1=1';
            $params = [];
            if ($q !== '') {
                $where .= ' AND (h.name LIKE :q OR h.city LIKE :q OR h.country LIKE :q OR h.price_from LIKE :q OR d.name LIKE :q)';
                $params[':q'] = '%' . $q . '%';
            }
            $from = 'hotels h LEFT JOIN destinations d ON d.id = h.destination_id';
            $cst = $pdo->prepare("SELECT COUNT(*) AS cnt FROM {$from} WHERE {$where}");
            foreach ($params as $k => $v) {
                $cst->bindValue($k, $v);
            }
            $cst->execute();
            $total = (int) (($cst->fetch()['cnt'] ?? 0));

            $stmt = $pdo->prepare(
                "SELECT h.*, d.name AS destination_name FROM {$from} WHERE {$where} ORDER BY h.is_featured DESC, h.name ASC, h.id DESC LIMIT :limit OFFSET :offset"
            );
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $pageCount = (int) max(1, (int) ceil($total / $perPage));

            return ['rows' => $rows, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pageCount' => $pageCount];
        }, ['rows' => [], 'total' => 0, 'page' => $page, 'perPage' => $perPage, 'pageCount' => 1]);
    }

    public static function create(array $data): int
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO hotels (destination_id, name, city, country, price_from, is_featured, is_active) VALUES (:did,:name,:city,:country,:price,:feat,:active)'
        );
        $dest = $data['destination_id'] ?? null;
        $stmt->execute([
            ':did' => $dest === null || $dest === '' ? null : (int) $dest,
            ':name' => (string) ($data['name'] ?? ''),
            ':city' => (string) ($data['city'] ?? ''),
            ':country' => (string) ($data['country'] ?? ''),
            ':price' => (string) ($data['price_from'] ?? ''),
            ':feat' => (int) ($data['is_featured'] ?? 0) === 1 ? 1 : 0,
            ':active' => (int) ($data['is_active'] ?? 1) === 0 ? 0 : 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = self::pdo()->prepare(
            'UPDATE hotels SET destination_id=:did, name=:name, city=:city, country=:country, price_from=:price, is_featured=:feat, is_active=:active, updated_at=NOW() WHERE id=:id'
        );
        $dest = $data['destination_id'] ?? null;
        $did = ($dest === null || $dest === '') ? null : (int) $dest;
        return $stmt->execute([
            ':id' => $id,
            ':did' => $did,
            ':name' => (string) ($data['name'] ?? ''),
            ':city' => (string) ($data['city'] ?? ''),
            ':country' => (string) ($data['country'] ?? ''),
            ':price' => (string) ($data['price_from'] ?? ''),
            ':feat' => (int) ($data['is_featured'] ?? 0) === 1 ? 1 : 0,
            ':active' => (int) ($data['is_active'] ?? 1) === 0 ? 0 : 1,
        ]);
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM hotels WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
