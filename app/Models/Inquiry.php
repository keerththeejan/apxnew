<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Inquiry extends Model
{
    public static function create(array $data): void
    {
        $stmt = self::pdo()->prepare('INSERT INTO inquiries (name, phone, email, service, message, created_at, updated_at) VALUES (:name, :phone, :email, :service, :message, NOW(), NOW())');
        $stmt->execute([
            ':name' => (string) ($data['name'] ?? ''),
            ':phone' => (string) ($data['phone'] ?? ''),
            ':email' => (string) ($data['email'] ?? ''),
            ':service' => (string) ($data['service'] ?? ''),
            ':message' => (string) ($data['message'] ?? ''),
        ]);
    }

    public static function latest(int $limit): array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM inquiries ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countAll(): int
    {
        $stmt = self::pdo()->query('SELECT COUNT(*) AS c FROM inquiries');
        $row = $stmt->fetch();
        return (int) ($row['c'] ?? 0);
    }

    /**
     * @return array{rows: list<array<string,mixed>>, total: int, page: int, perPage: int, pageCount: int}
     */
    public static function paginate(string $q, string $status, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $pdo = self::pdo();
        $where = ['1=1'];
        $params = [];
        $q = trim($q);
        if ($q !== '') {
            $where[] = '(name LIKE :q OR phone LIKE :q OR email LIKE :q OR message LIKE :q OR service LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($status !== '' && $status !== 'all') {
            $where[] = 'status = :st';
            $params[':st'] = $status;
        }
        $whereSql = implode(' AND ', $where);
        $cst = $pdo->prepare("SELECT COUNT(*) AS cnt FROM inquiries WHERE {$whereSql}");
        foreach ($params as $k => $v) {
            $cst->bindValue($k, $v);
        }
        $cst->execute();
        $total = (int) (($cst->fetch()['cnt'] ?? 0));
        $stmt = $pdo->prepare("SELECT * FROM inquiries WHERE {$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset");
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

    public static function updateStatus(int $id, string $status): bool
    {
        $stmt = self::pdo()->prepare('UPDATE inquiries SET status = :s WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':s', $status);
        return $stmt->execute();
    }
}
