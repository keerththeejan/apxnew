<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ContactMessage extends Model
{
    public static function create(array $data): void
    {
        $stmt = self::pdo()->prepare('INSERT INTO contact_messages (name, email, phone, subject, message, is_read, created_at, updated_at) VALUES (:name, :email, :phone, :subject, :message, 0, NOW(), NOW())');
        $stmt->execute([
            ':name' => (string) ($data['name'] ?? ''),
            ':email' => (string) ($data['email'] ?? ''),
            ':phone' => (string) ($data['phone'] ?? ''),
            ':subject' => (string) ($data['subject'] ?? ''),
            ':message' => (string) ($data['message'] ?? ''),
        ]);
    }

    public static function latest(int $limit): array
    {
        return self::safe(function () use ($limit): array {
            $stmt = self::pdo()->prepare('SELECT * FROM contact_messages ORDER BY id DESC LIMIT :lim');
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function countAll(): int
    {
        return self::safe(function (): int {
            $stmt = self::pdo()->query('SELECT COUNT(*) AS c FROM contact_messages');
            $row = $stmt->fetch();
            return (int) ($row['c'] ?? 0);
        }, 0);
    }

    public static function findById(int $id): ?array
    {
        return self::safe(function () use ($id): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row === false ? null : $row;
        }, null);
    }

    /**
     * @param 'all'|'unread'|'read' $filter
     * @return array{rows: list<array<string,mixed>>, total: int, page: int, perPage: int, pageCount: int}
     */
    public static function paginate(string $q, string $filter, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $q = trim($q);
        $filter = strtolower(trim($filter));
        if (!in_array($filter, ['all', 'unread', 'read'], true)) {
            $filter = 'all';
        }

        return self::safe(function () use ($q, $filter, $page, $perPage, $offset): array {
            $pdo = self::pdo();
            $where = '1=1';
            $params = [];
            if ($q !== '') {
                $where .= ' AND (name LIKE :q OR email LIKE :q OR subject LIKE :q OR message LIKE :q OR phone LIKE :q)';
                $params[':q'] = '%' . $q . '%';
            }
            if ($filter === 'unread') {
                $where .= ' AND is_read = 0';
            } elseif ($filter === 'read') {
                $where .= ' AND is_read = 1';
            }
            $cst = $pdo->prepare("SELECT COUNT(*) AS cnt FROM contact_messages WHERE {$where}");
            foreach ($params as $k => $v) {
                $cst->bindValue($k, $v);
            }
            $cst->execute();
            $total = (int) (($cst->fetch()['cnt'] ?? 0));
            $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset");
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

    public static function markRead(int $id): bool
    {
        $stmt = self::pdo()->prepare('UPDATE contact_messages SET is_read = 1, updated_at = NOW() WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function markUnread(int $id): bool
    {
        $stmt = self::pdo()->prepare('UPDATE contact_messages SET is_read = 0, updated_at = NOW() WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM contact_messages WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
