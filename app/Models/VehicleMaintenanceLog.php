<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class VehicleMaintenanceLog extends Model
{
    public static function schemaReady(): bool
    {
        return self::safe(function (): bool {
            self::pdo()->query('SELECT 1 FROM vehicle_maintenance_logs LIMIT 1');

            return true;
        }, false);
    }

    /** @return array{rows:list<array<string,mixed>>, total:int, page:int, perPage:int, pageCount:int} */
    public static function paginate(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        return self::safe(function () use ($page, $perPage, $offset): array {
            $pdo = self::pdo();
            $total = (int) (($pdo->query('SELECT COUNT(*) AS cnt FROM vehicle_maintenance_logs')->fetch()['cnt'] ?? 0));
            $stmt = $pdo->prepare('SELECT m.*, v.name AS vehicle_name FROM vehicle_maintenance_logs m LEFT JOIN vehicles v ON v.id = m.vehicle_id ORDER BY m.maintenance_date DESC, m.id DESC LIMIT :limit OFFSET :offset');
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'rows' => $stmt->fetchAll() ?: [],
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'pageCount' => (int) max(1, (int) ceil($total / $perPage)),
            ];
        }, ['rows' => [], 'total' => 0, 'page' => $page, 'perPage' => $perPage, 'pageCount' => 1]);
    }

    /** @param array<string,mixed> $data */
    public static function create(array $data): int
    {
        $stmt = self::pdo()->prepare('INSERT INTO vehicle_maintenance_logs (vehicle_id, title, details, maintenance_date, next_due_date, status) VALUES (:vehicle_id,:title,:details,:maintenance_date,:next_due_date,:status)');
        $stmt->execute([
            ':vehicle_id' => (int) ($data['vehicle_id'] ?? 0),
            ':title' => trim((string) ($data['title'] ?? '')),
            ':details' => trim((string) ($data['details'] ?? '')),
            ':maintenance_date' => trim((string) ($data['maintenance_date'] ?? date('Y-m-d'))),
            ':next_due_date' => trim((string) ($data['next_due_date'] ?? '')) ?: null,
            ':status' => trim((string) ($data['status'] ?? 'scheduled')),
        ]);

        return (int) self::pdo()->lastInsertId();
    }

    public static function delete(int $id): bool
    {
        $stmt = self::pdo()->prepare('DELETE FROM vehicle_maintenance_logs WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }
}
