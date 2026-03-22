<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;
use PDO;

final class Page
{
    public static function findByKey(string $key): ?array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM pages WHERE `key` = :k AND is_active = 1 LIMIT 1');
        $stmt->execute([':k' => $key]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function paginate(string $q, int $page, int $perPage, string $sort, string $dir): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $allowedSort = ['id', 'key', 'title', 'slug', 'is_active', 'updated_at', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'updated_at';
        }
        $dir = strtoupper($dir);
        if ($dir !== 'ASC' && $dir !== 'DESC') {
            $dir = 'DESC';
        }

        $pdo = Db::pdo();

        $whereSql = '';
        $params = [];
        $q = trim($q);
        if ($q !== '') {
            $whereSql = 'WHERE `key` LIKE :q OR title LIKE :q OR slug LIKE :q';
            $params[':q'] = '%' . $q . '%';
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM pages {$whereSql}");
        foreach ($params as $k => $v) {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();
        $total = (int) (($countStmt->fetch()['cnt'] ?? 0));

        $stmt = $pdo->prepare("SELECT * FROM pages {$whereSql} ORDER BY `{$sort}` {$dir} LIMIT :limit OFFSET :offset");
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

    public static function create(array $data, ?int $adminId = null): int
    {
        $pdo = Db::pdo();
        $key = (string) ($data['key'] ?? '');
        $title = (string) ($data['title'] ?? '');
        $slug = $data['slug'] ?? null;
        $slugVal = $slug === '' ? null : $slug;
        $content = (string) ($data['content'] ?? '');
        $mt = $data['meta_title'] ?? null;
        $md = $data['meta_description'] ?? null;
        $active = (int) ($data['is_active'] ?? 1);

        try {
            $stmt = $pdo->prepare('INSERT INTO pages (`key`, title, slug, content, meta_title, meta_description, is_active, updated_by_admin_id) VALUES (:key, :title, :slug, :content, :meta_title, :meta_description, :is_active, :admin_id)');
            $stmt->bindValue(':key', $key);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':slug', $slugVal, $slugVal === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':content', $content);
            $stmt->bindValue(':meta_title', $mt === '' || $mt === null ? null : (string) $mt, $mt === '' || $mt === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':meta_description', $md === '' || $md === null ? null : (string) $md, $md === '' || $md === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':is_active', $active, PDO::PARAM_INT);
            $stmt->bindValue(':admin_id', $adminId, $adminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            if (!self::isMissingColumnError($e)) {
                throw $e;
            }
            $stmt = $pdo->prepare('INSERT INTO pages (`key`, title, slug, content, is_active, updated_by_admin_id) VALUES (:key, :title, :slug, :content, :is_active, :admin_id)');
            $stmt->bindValue(':key', $key);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':slug', $slugVal, $slugVal === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':content', $content);
            $stmt->bindValue(':is_active', $active, PDO::PARAM_INT);
            $stmt->bindValue(':admin_id', $adminId, $adminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->execute();
        }

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data, ?int $adminId = null): bool
    {
        $pdo = Db::pdo();
        $key = (string) ($data['key'] ?? '');
        $title = (string) ($data['title'] ?? '');
        $slug = $data['slug'] ?? null;
        $slugVal = $slug === '' ? null : $slug;
        $content = (string) ($data['content'] ?? '');
        $mt = $data['meta_title'] ?? null;
        $md = $data['meta_description'] ?? null;
        $active = (int) ($data['is_active'] ?? 1);

        try {
            $stmt = $pdo->prepare('UPDATE pages SET `key`=:key, title=:title, slug=:slug, content=:content, meta_title=:meta_title, meta_description=:meta_description, is_active=:is_active, updated_by_admin_id=:admin_id WHERE id=:id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':key', $key);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':slug', $slugVal, $slugVal === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':content', $content);
            $stmt->bindValue(':meta_title', $mt === '' || $mt === null ? null : (string) $mt, $mt === '' || $mt === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':meta_description', $md === '' || $md === null ? null : (string) $md, $md === '' || $md === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':is_active', $active, PDO::PARAM_INT);
            $stmt->bindValue(':admin_id', $adminId, $adminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            if (!self::isMissingColumnError($e)) {
                throw $e;
            }
            $stmt = $pdo->prepare('UPDATE pages SET `key`=:key, title=:title, slug=:slug, content=:content, is_active=:is_active, updated_by_admin_id=:admin_id WHERE id=:id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':key', $key);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':slug', $slugVal, $slugVal === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':content', $content);
            $stmt->bindValue(':is_active', $active, PDO::PARAM_INT);
            $stmt->bindValue(':admin_id', $adminId, $adminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

            return $stmt->execute();
        }
    }

    private static function isMissingColumnError(\PDOException $e): bool
    {
        $m = $e->getMessage();

        return str_contains($m, '42S22') || str_contains($m, 'Unknown column');
    }

    public static function delete(int $id): int
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('DELETE FROM pages WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
