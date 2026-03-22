<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class InsurancePackage extends Model
{
    /** @return list<array<string,mixed>> */
    public static function active(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM insurance_packages WHERE is_active = 1 ORDER BY sort_order ASC, id DESC');
            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function findById(int $id): ?array
    {
        return self::safe(function () use ($id): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM insurance_packages WHERE id = :id LIMIT 1');
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
                $where .= ' AND (name LIKE :q OR summary LIKE :q OR coverage_text LIKE :q OR price_from LIKE :q)';
                $params[':q'] = '%' . $q . '%';
            }
            $cst = $pdo->prepare("SELECT COUNT(*) AS cnt FROM insurance_packages WHERE {$where}");
            foreach ($params as $k => $v) {
                $cst->bindValue($k, $v);
            }
            $cst->execute();
            $total = (int) (($cst->fetch()['cnt'] ?? 0));
            $stmt = $pdo->prepare("SELECT * FROM insurance_packages WHERE {$where} ORDER BY sort_order ASC, id DESC LIMIT :limit OFFSET :offset");
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
            'INSERT INTO insurance_packages (name, summary, coverage_text, price_from, sort_order, is_active) VALUES (:name,:sum,:cov,:price,:sort,:active)'
        );
        $stmt->execute([
            ':name' => (string) ($data['name'] ?? ''),
            ':sum' => (string) ($data['summary'] ?? ''),
            ':cov' => (string) ($data['coverage_text'] ?? ''),
            ':price' => (string) ($data['price_from'] ?? ''),
            ':sort' => (int) ($data['sort_order'] ?? 0),
            ':active' => (int) ($data['is_active'] ?? 1),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = self::pdo()->prepare(
            'UPDATE insurance_packages SET name=:name, summary=:sum, coverage_text=:cov, price_from=:price, sort_order=:sort, is_active=:active, updated_at=NOW() WHERE id=:id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', (string) ($data['name'] ?? ''));
        $stmt->bindValue(':sum', (string) ($data['summary'] ?? ''));
        $stmt->bindValue(':cov', (string) ($data['coverage_text'] ?? ''));
        $stmt->bindValue(':price', (string) ($data['price_from'] ?? ''));
        $stmt->bindValue(':sort', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':active', (int) ($data['is_active'] ?? 1), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM insurance_packages WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
