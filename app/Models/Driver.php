<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\VehicleModuleSchema;
use PDO;

final class Driver extends Model
{
    public static function schemaReady(): bool
    {
        return self::safe(function (): bool {
            VehicleModuleSchema::ensure(self::pdo());
            self::pdo()->query('SELECT 1 FROM drivers LIMIT 1');

            return true;
        }, false);
    }

    /** @return list<array<string,mixed>> */
    public static function allOrdered(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM drivers ORDER BY is_active DESC, name ASC, id DESC');

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    /** @return list<array<string,mixed>> */
    public static function available(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query("SELECT * FROM drivers WHERE is_active = 1 AND status = 'available' ORDER BY name ASC, id DESC");

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        return self::safe(function () use ($id): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM drivers WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            return $row === false ? null : $row;
        }, null);
    }

    public static function findByEmail(string $email): ?array
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }
        return self::safe(function () use ($email): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM drivers WHERE email = :email AND is_active = 1 LIMIT 1');
            $stmt->execute([':email' => $email]);
            $row = $stmt->fetch();

            return $row === false ? null : $row;
        }, null);
    }

    /** @return array{rows:list<array<string,mixed>>, total:int, page:int, perPage:int, pageCount:int} */
    public static function paginate(string $q, string $status, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $q = trim($q);
        $status = trim($status);

        return self::safe(function () use ($q, $status, $page, $perPage, $offset): array {
            $where = '1=1';
            $params = [];
            if ($q !== '') {
                $where .= ' AND (name LIKE :q OR phone LIKE :q OR license_number LIKE :q)';
                $params[':q'] = '%' . $q . '%';
            }
            if ($status !== '') {
                $where .= ' AND status = :s';
                $params[':s'] = $status;
            }

            $pdo = self::pdo();
            $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM drivers WHERE {$where}");
            $countStmt->execute($params);
            $total = (int) (($countStmt->fetch()['cnt'] ?? 0));

            $stmt = $pdo->prepare("SELECT * FROM drivers WHERE {$where} ORDER BY is_active DESC, name ASC, id DESC LIMIT :limit OFFSET :offset");
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
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
        $stmt = self::pdo()->prepare(
            'INSERT INTO drivers (branch_id, vehicle_id, name, phone, email, license_number, profile_image_path, status, is_active)
             VALUES (:branch_id,:vehicle_id,:name,:phone,:email,:license_number,:profile_image_path,:status,:is_active)'
        );
        $stmt->execute([
            ':branch_id' => self::nullInt($data['branch_id'] ?? null),
            ':vehicle_id' => self::nullInt($data['vehicle_id'] ?? null),
            ':name' => trim((string) ($data['name'] ?? '')),
            ':phone' => trim((string) ($data['phone'] ?? '')),
            ':email' => trim((string) ($data['email'] ?? '')),
            ':license_number' => trim((string) ($data['license_number'] ?? '')),
            ':profile_image_path' => trim((string) ($data['profile_image_path'] ?? '')),
            ':status' => trim((string) ($data['status'] ?? 'available')),
            ':is_active' => (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
        ]);

        return (int) self::pdo()->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public static function update(int $id, array $data): bool
    {
        $stmt = self::pdo()->prepare(
            'UPDATE drivers SET branch_id=:branch_id, vehicle_id=:vehicle_id, name=:name, phone=:phone, email=:email, license_number=:license_number, profile_image_path=:profile_image_path, status=:status, is_active=:is_active, updated_at=NOW() WHERE id=:id'
        );

        return $stmt->execute([
            ':id' => $id,
            ':branch_id' => self::nullInt($data['branch_id'] ?? null),
            ':vehicle_id' => self::nullInt($data['vehicle_id'] ?? null),
            ':name' => trim((string) ($data['name'] ?? '')),
            ':phone' => trim((string) ($data['phone'] ?? '')),
            ':email' => trim((string) ($data['email'] ?? '')),
            ':license_number' => trim((string) ($data['license_number'] ?? '')),
            ':profile_image_path' => trim((string) ($data['profile_image_path'] ?? '')),
            ':status' => trim((string) ($data['status'] ?? 'available')),
            ':is_active' => (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
        ]);
    }

    public static function delete(int $id): bool
    {
        $stmt = self::pdo()->prepare('DELETE FROM drivers WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    private static function nullInt(mixed $value): ?int
    {
        $x = (int) $value;

        return $x > 0 ? $x : null;
    }
}
