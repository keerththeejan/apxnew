<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Vehicle extends Model
{
    public static function schemaReady(): bool
    {
        return self::safe(function (): bool {
            self::pdo()->query('SELECT 1 FROM vehicles LIMIT 1');

            return true;
        }, false);
    }

    /** @return list<array<string, mixed>> */
    public static function allOrdered(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM vehicles ORDER BY is_active DESC, name ASC, id DESC');

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    /** @return list<array<string, mixed>> */
    public static function availableByType(string $vehicleType): array
    {
        return self::safe(function () use ($vehicleType): array {
            $sql = 'SELECT * FROM vehicles WHERE is_active = 1 AND availability_status = \'available\'';
            $params = [];
            if ($vehicleType !== '') {
                $sql .= ' AND vehicle_type = :t';
                $params[':t'] = $vehicleType;
            }
            $sql .= ' ORDER BY name ASC, id DESC';
            $stmt = self::pdo()->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        return self::safe(function () use ($id): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM vehicles WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            return $row === false ? null : $row;
        }, null);
    }

    /** @return array{rows:list<array<string,mixed>>, total:int, page:int, perPage:int, pageCount:int} */
    public static function paginate(string $q, string $type, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $q = trim($q);
        $type = trim($type);

        return self::safe(function () use ($q, $type, $page, $perPage, $offset): array {
            $where = '1=1';
            $params = [];
            if ($q !== '') {
                $where .= ' AND (name LIKE :q OR model LIKE :q OR registration_number LIKE :q)';
                $params[':q'] = '%' . $q . '%';
            }
            if ($type !== '') {
                $where .= ' AND vehicle_type = :t';
                $params[':t'] = $type;
            }

            $pdo = self::pdo();
            $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM vehicles WHERE {$where}");
            $countStmt->execute($params);
            $total = (int) (($countStmt->fetch()['cnt'] ?? 0));

            $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE {$where} ORDER BY is_active DESC, name ASC, id DESC LIMIT :limit OFFSET :offset");
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll() ?: [];

            return [
                'rows' => $rows,
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
        $stmt = self::pdo()->prepare(
            'INSERT INTO vehicles (branch_id, name, vehicle_type, model, registration_number, seating_capacity, luggage_capacity, fuel_type, image_path, availability_status, pricing_json, is_active)
             VALUES (:branch_id,:name,:vehicle_type,:model,:registration_number,:seating_capacity,:luggage_capacity,:fuel_type,:image_path,:availability_status,:pricing_json,:is_active)'
        );
        $stmt->execute([
            ':branch_id' => self::nullInt($data['branch_id'] ?? null),
            ':name' => trim((string) ($data['name'] ?? '')),
            ':vehicle_type' => trim((string) ($data['vehicle_type'] ?? 'car')),
            ':model' => trim((string) ($data['model'] ?? '')),
            ':registration_number' => strtoupper(trim((string) ($data['registration_number'] ?? ''))),
            ':seating_capacity' => (int) ($data['seating_capacity'] ?? 1),
            ':luggage_capacity' => (int) ($data['luggage_capacity'] ?? 0),
            ':fuel_type' => trim((string) ($data['fuel_type'] ?? '')),
            ':image_path' => trim((string) ($data['image_path'] ?? '')),
            ':availability_status' => trim((string) ($data['availability_status'] ?? 'available')),
            ':pricing_json' => is_string($data['pricing_json'] ?? null) ? $data['pricing_json'] : json_encode($data['pricing_json'] ?? [], JSON_UNESCAPED_UNICODE),
            ':is_active' => (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
        ]);

        return (int) self::pdo()->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public static function update(int $id, array $data): bool
    {
        $stmt = self::pdo()->prepare(
            'UPDATE vehicles SET branch_id=:branch_id, name=:name, vehicle_type=:vehicle_type, model=:model, registration_number=:registration_number, seating_capacity=:seating_capacity, luggage_capacity=:luggage_capacity, fuel_type=:fuel_type, image_path=:image_path, availability_status=:availability_status, pricing_json=:pricing_json, is_active=:is_active, updated_at=NOW() WHERE id=:id'
        );

        return $stmt->execute([
            ':id' => $id,
            ':branch_id' => self::nullInt($data['branch_id'] ?? null),
            ':name' => trim((string) ($data['name'] ?? '')),
            ':vehicle_type' => trim((string) ($data['vehicle_type'] ?? 'car')),
            ':model' => trim((string) ($data['model'] ?? '')),
            ':registration_number' => strtoupper(trim((string) ($data['registration_number'] ?? ''))),
            ':seating_capacity' => (int) ($data['seating_capacity'] ?? 1),
            ':luggage_capacity' => (int) ($data['luggage_capacity'] ?? 0),
            ':fuel_type' => trim((string) ($data['fuel_type'] ?? '')),
            ':image_path' => trim((string) ($data['image_path'] ?? '')),
            ':availability_status' => trim((string) ($data['availability_status'] ?? 'available')),
            ':pricing_json' => is_string($data['pricing_json'] ?? null) ? $data['pricing_json'] : json_encode($data['pricing_json'] ?? [], JSON_UNESCAPED_UNICODE),
            ':is_active' => (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
        ]);
    }

    public static function delete(int $id): bool
    {
        $stmt = self::pdo()->prepare('DELETE FROM vehicles WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    private static function nullInt(mixed $value): ?int
    {
        $x = (int) $value;

        return $x > 0 ? $x : null;
    }
}
