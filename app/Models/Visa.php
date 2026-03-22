<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Visa extends Model
{
    public static function active(): array
    {
        $stmt = self::pdo()->query('SELECT * FROM visas WHERE is_active = 1 ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM visas WHERE id = :id LIMIT 1');
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
            $where .= ' AND (title LIKE :q OR summary LIKE :q OR requirements LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        $cst = $pdo->prepare("SELECT COUNT(*) AS cnt FROM visas WHERE {$where}");
        foreach ($params as $k => $v) {
            $cst->bindValue($k, $v);
        }
        $cst->execute();
        $total = (int) (($cst->fetch()['cnt'] ?? 0));
        $stmt = $pdo->prepare("SELECT * FROM visas WHERE {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset");
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
        $stmt = $pdo->prepare(
            'INSERT INTO visas (destination_id, title, summary, requirements, processing_days, fee_from, is_active) VALUES (:did,:title,:sum,:req,:days,:fee,:active)'
        );
        $did = $data['destination_id'] ?? null;
        $stmt->bindValue(':did', $did === '' || $did === null ? null : (int) $did, $did === '' || $did === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':title', (string) ($data['title'] ?? ''));
        $stmt->bindValue(':sum', (string) ($data['summary'] ?? ''));
        $stmt->bindValue(':req', (string) ($data['requirements'] ?? ''));
        $pd = $data['processing_days'] ?? null;
        if ($pd === '' || $pd === null) {
            $stmt->bindValue(':days', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':days', (int) $pd, PDO::PARAM_INT);
        }
        $fee = $data['fee_from'] ?? null;
        if ($fee === '' || $fee === null) {
            $stmt->bindValue(':fee', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':fee', (float) $fee);
        }
        $stmt->bindValue(':active', (int) ($data['is_active'] ?? 1), PDO::PARAM_INT);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare(
            'UPDATE visas SET destination_id = :did, title = :title, summary = :sum, requirements = :req, processing_days = :days, fee_from = :fee, is_active = :active, updated_at = NOW() WHERE id = :id'
        );
        $did = $data['destination_id'] ?? null;
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':did', $did === '' || $did === null ? null : (int) $did, $did === '' || $did === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':title', (string) ($data['title'] ?? ''));
        $stmt->bindValue(':sum', (string) ($data['summary'] ?? ''));
        $stmt->bindValue(':req', (string) ($data['requirements'] ?? ''));
        $pd = $data['processing_days'] ?? null;
        $stmt->bindValue(':days', $pd === '' || $pd === null ? null : (int) $pd, $pd === '' || $pd === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $fee = $data['fee_from'] ?? null;
        $stmt->bindValue(':fee', $fee === '' || $fee === null ? null : (float) $fee, $fee === '' || $fee === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':active', (int) ($data['is_active'] ?? 1), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM visas WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
