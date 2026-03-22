<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class FinanceService extends Model
{
    /** True when the finance_services table exists (migration applied). */
    public static function schemaReady(): bool
    {
        return self::safe(function (): bool {
            self::pdo()->query('SELECT 1 FROM finance_services LIMIT 1');
            return true;
        }, false);
    }

    /** @return list<array<string,mixed>> */
    public static function active(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query("SELECT * FROM finance_services WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function findById(int $id): ?array
    {
        return self::safe(function () use ($id): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM finance_services WHERE id = :id LIMIT 1');
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
                $where .= ' AND (title LIKE :q OR description LIKE :q)';
                $params[':q'] = '%' . $q . '%';
            }
            $cst = $pdo->prepare("SELECT COUNT(*) AS cnt FROM finance_services WHERE {$where}");
            foreach ($params as $k => $v) {
                $cst->bindValue($k, $v);
            }
            $cst->execute();
            $total = (int) (($cst->fetch()['cnt'] ?? 0));
            $stmt = $pdo->prepare("SELECT * FROM finance_services WHERE {$where} ORDER BY sort_order ASC, id DESC LIMIT :limit OFFSET :offset");
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
            'INSERT INTO finance_services (title, description, status, sort_order) VALUES (:title,:desc,:st,:sort)'
        );
        $stmt->execute([
            ':title' => (string) ($data['title'] ?? ''),
            ':desc' => (string) ($data['description'] ?? ''),
            ':st' => (string) ($data['status'] ?? 'draft'),
            ':sort' => (int) ($data['sort_order'] ?? 0),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = self::pdo()->prepare(
            'UPDATE finance_services SET title=:title, description=:desc, status=:st, sort_order=:sort, updated_at=NOW() WHERE id=:id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':title', (string) ($data['title'] ?? ''));
        $stmt->bindValue(':desc', (string) ($data['description'] ?? ''));
        $stmt->bindValue(':st', (string) ($data['status'] ?? 'draft'));
        $stmt->bindValue(':sort', (int) ($data['sort_order'] ?? 0), PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM finance_services WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
