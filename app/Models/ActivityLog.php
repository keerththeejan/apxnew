<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ActivityLog extends Model
{
    /**
     * @param array<string, mixed>|null $meta
     */
    public static function record(?int $adminId, string $action, ?string $entity = null, ?int $entityId = null, ?array $meta = null): void
    {
        try {
            $stmt = self::pdo()->prepare(
                'INSERT INTO activity_logs (admin_id, action, entity, entity_id, meta_json, ip) VALUES (:aid, :act, :ent, :eid, :meta, :ip)'
            );
            $stmt->execute([
                ':aid' => $adminId,
                ':act' => $action,
                ':ent' => $entity,
                ':eid' => $entityId,
                ':meta' => $meta !== null ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                ':ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            ]);
        } catch (\Throwable $e) {
            // table may not exist on older installs
        }
    }

    /**
     * @return array{rows: list<array<string,mixed>>, total: int, page: int, perPage: int, pageCount: int}
     */
    public static function paginate(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $pdo = self::pdo();
        $total = (int) ($pdo->query('SELECT COUNT(*) AS c FROM activity_logs')->fetch()['c'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM activity_logs ORDER BY id DESC LIMIT :lim OFFSET :off');
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $pageCount = (int) max(1, (int) ceil($total / $perPage));

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pageCount' => $pageCount];
    }
}
