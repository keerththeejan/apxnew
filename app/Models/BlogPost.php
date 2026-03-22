<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class BlogPost extends Model
{
    public static function latest(int $limit): array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM blog_posts WHERE status = "published" ORDER BY published_at DESC, id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM blog_posts WHERE slug = :slug AND status = "published" LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM blog_posts WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function countAll(): int
    {
        $stmt = self::pdo()->query('SELECT COUNT(*) AS c FROM blog_posts');
        $row = $stmt->fetch();
        return (int) ($row['c'] ?? 0);
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
            $where .= ' AND (title LIKE :q OR slug LIKE :q OR excerpt LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        $cst = $pdo->prepare("SELECT COUNT(*) AS cnt FROM blog_posts WHERE {$where}");
        foreach ($params as $k => $v) {
            $cst->bindValue($k, $v);
        }
        $cst->execute();
        $total = (int) (($cst->fetch()['cnt'] ?? 0));
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset");
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

    public static function create(array $data, ?int $adminId = null): int
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare('INSERT INTO blog_posts (author_admin_id, title, slug, excerpt, content, cover_image_path, status, published_at) VALUES (:aid,:title,:slug,:ex,:content,:cover,:status,:pub)');
        $stmt->bindValue(':aid', $adminId, $adminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':title', (string) ($data['title'] ?? ''));
        $stmt->bindValue(':slug', (string) ($data['slug'] ?? ''));
        $stmt->bindValue(':ex', (string) ($data['excerpt'] ?? ''));
        $stmt->bindValue(':content', (string) ($data['content'] ?? ''));
        $cv = $data['cover_image_path'] ?? null;
        $stmt->bindValue(':cover', $cv === '' || $cv === null ? null : (string) $cv, $cv === '' || $cv === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':status', (string) ($data['status'] ?? 'draft'));
        $pub = $data['published_at'] ?? null;
        $stmt->bindValue(':pub', $pub === '' || $pub === null ? null : (string) $pub, $pub === '' || $pub === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = self::pdo()->prepare('UPDATE blog_posts SET title=:title, slug=:slug, excerpt=:ex, content=:content, cover_image_path=:cover, status=:status, published_at=:pub WHERE id=:id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':title', (string) ($data['title'] ?? ''));
        $stmt->bindValue(':slug', (string) ($data['slug'] ?? ''));
        $stmt->bindValue(':ex', (string) ($data['excerpt'] ?? ''));
        $stmt->bindValue(':content', (string) ($data['content'] ?? ''));
        $cv = $data['cover_image_path'] ?? null;
        $stmt->bindValue(':cover', $cv === '' || $cv === null ? null : (string) $cv, $cv === '' || $cv === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':status', (string) ($data['status'] ?? 'draft'));
        $pub = $data['published_at'] ?? null;
        $stmt->bindValue(':pub', $pub === '' || $pub === null ? null : (string) $pub, $pub === '' || $pub === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM blog_posts WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
