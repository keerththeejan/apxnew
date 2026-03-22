<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Application extends Model
{
    /**
     * @param array{name?:string,email?:string,phone?:string,service_type?:string,message?:string,status?:string,form_data_json?:string} $data
     */
    public static function create(array $data): void
    {
        $pdo = self::pdo();
        $params = [
            ':name' => (string) ($data['name'] ?? ''),
            ':email' => (string) ($data['email'] ?? ''),
            ':phone' => (string) ($data['phone'] ?? ''),
            ':service_type' => (string) ($data['service_type'] ?? ''),
            ':message' => (string) ($data['message'] ?? ''),
            ':status' => (string) ($data['status'] ?? 'pending'),
            ':form_json' => (string) ($data['form_data_json'] ?? ''),
        ];
        try {
            $stmt = $pdo->prepare('INSERT INTO applications (name, email, phone, service_type, message, status, form_data_json, is_contacted, created_at, updated_at) VALUES (:name, :email, :phone, :service_type, :message, :status, :form_json, 0, NOW(), NOW())');
            $stmt->execute($params);
        } catch (\PDOException $e) {
            $m = $e->getMessage();
            if (!str_contains($m, '42S22') && !str_contains($m, 'Unknown column')) {
                throw $e;
            }
            $stmt = $pdo->prepare('INSERT INTO applications (name, email, phone, service_type, message, is_contacted, created_at, updated_at) VALUES (:name, :email, :phone, :service_type, :message, 0, NOW(), NOW())');
            $stmt->execute([
                ':name' => $params[':name'],
                ':email' => $params[':email'],
                ':phone' => $params[':phone'],
                ':service_type' => $params[':service_type'],
                ':message' => $params[':message'],
            ]);
        }
    }

    public static function latest(int $limit): array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM applications ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countAll(): int
    {
        $stmt = self::pdo()->query('SELECT COUNT(*) AS c FROM applications');
        $row = $stmt->fetch();
        return (int) ($row['c'] ?? 0);
    }

    public static function countByStatus(string $status): int
    {
        $stmt = self::pdo()->prepare('SELECT COUNT(*) AS c FROM applications WHERE status = :s');
        $stmt->execute([':s' => $status]);
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
            $where[] = '(name LIKE :q OR email LIKE :q OR phone LIKE :q OR service_type LIKE :q OR message LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($status !== '' && $status !== 'all') {
            $where[] = 'status = :st';
            $params[':st'] = $status;
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM applications WHERE {$whereSql}");
        foreach ($params as $k => $v) {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();
        $total = (int) (($countStmt->fetch()['cnt'] ?? 0));

        $stmt = $pdo->prepare("SELECT * FROM applications WHERE {$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset");
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

    /**
     * Applications whose service type indicates visa (e.g. "Visa Services").
     *
     * @return array{rows: list<array<string,mixed>>, total: int, page: int, perPage: int, pageCount: int}
     */
    public static function paginateVisaRelated(string $q, string $status, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $pdo = self::pdo();
        $where = ['(service_type IS NOT NULL AND LOWER(service_type) LIKE :vpat)'];
        $params = [':vpat' => '%visa%'];
        $q = trim($q);
        if ($q !== '') {
            $where[] = '(name LIKE :q OR email LIKE :q OR phone LIKE :q OR message LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        if ($status !== '' && $status !== 'all') {
            $where[] = 'status = :st';
            $params[':st'] = $status;
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM applications WHERE {$whereSql}");
        foreach ($params as $k => $v) {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();
        $total = (int) (($countStmt->fetch()['cnt'] ?? 0));

        $stmt = $pdo->prepare("SELECT * FROM applications WHERE {$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset");
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

    public static function updateStatus(int $id, string $status): bool
    {
        $stmt = self::pdo()->prepare('UPDATE applications SET status = :s, updated_at = NOW() WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':s', $status);
        return $stmt->execute();
    }
}
