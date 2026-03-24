<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class PricingRule extends Model
{
    public static function schemaReady(): bool
    {
        return self::safe(function (): bool {
            self::pdo()->query('SELECT 1 FROM pricing_rules LIMIT 1');

            return true;
        }, false);
    }

    /** @return list<array<string,mixed>> */
    public static function active(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM pricing_rules WHERE is_active = 1 ORDER BY vehicle_type ASC, id DESC');

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function findActiveByType(string $vehicleType, ?int $branchId = null): ?array
    {
        $vehicleType = trim($vehicleType);
        if ($vehicleType === '') {
            return null;
        }

        return self::safe(function () use ($vehicleType, $branchId): ?array {
            $sql = 'SELECT * FROM pricing_rules WHERE is_active = 1 AND vehicle_type = :t';
            $params = [':t' => $vehicleType];
            if ($branchId !== null && $branchId > 0) {
                $sql .= ' AND (branch_id = :b OR branch_id IS NULL)';
                $params[':b'] = $branchId;
            }
            $sql .= ' ORDER BY branch_id DESC, id DESC LIMIT 1';
            $stmt = self::pdo()->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();

            return $row === false ? null : $row;
        }, null);
    }

    /** @return array{rows:list<array<string,mixed>>, total:int, page:int, perPage:int, pageCount:int} */
    public static function paginate(string $vehicleType, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $vehicleType = trim($vehicleType);

        return self::safe(function () use ($vehicleType, $page, $perPage, $offset): array {
            $where = '1=1';
            $params = [];
            if ($vehicleType !== '') {
                $where .= ' AND vehicle_type = :t';
                $params[':t'] = $vehicleType;
            }
            $pdo = self::pdo();
            $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM pricing_rules WHERE {$where}");
            $countStmt->execute($params);
            $total = (int) (($countStmt->fetch()['cnt'] ?? 0));

            $stmt = $pdo->prepare("SELECT * FROM pricing_rules WHERE {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset");
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
            'INSERT INTO pricing_rules (branch_id, vehicle_type, base_fare, per_km, per_hour, per_day, waiting_per_hour, extra_km_charge, extra_km_threshold, night_charge_percent, peak_charge_percent, peak_start, peak_end, night_start, night_end, is_active)
             VALUES (:branch_id,:vehicle_type,:base_fare,:per_km,:per_hour,:per_day,:waiting_per_hour,:extra_km_charge,:extra_km_threshold,:night_charge_percent,:peak_charge_percent,:peak_start,:peak_end,:night_start,:night_end,:is_active)'
        );
        $stmt->execute([
            ':branch_id' => self::nullInt($data['branch_id'] ?? null),
            ':vehicle_type' => trim((string) ($data['vehicle_type'] ?? 'car')),
            ':base_fare' => self::money($data['base_fare'] ?? 0),
            ':per_km' => self::money($data['per_km'] ?? 0),
            ':per_hour' => self::money($data['per_hour'] ?? 0),
            ':per_day' => self::money($data['per_day'] ?? 0),
            ':waiting_per_hour' => self::money($data['waiting_per_hour'] ?? 0),
            ':extra_km_charge' => self::money($data['extra_km_charge'] ?? 0),
            ':extra_km_threshold' => self::money($data['extra_km_threshold'] ?? 0),
            ':night_charge_percent' => self::money($data['night_charge_percent'] ?? 0),
            ':peak_charge_percent' => self::money($data['peak_charge_percent'] ?? 0),
            ':peak_start' => self::timeOrNull($data['peak_start'] ?? null),
            ':peak_end' => self::timeOrNull($data['peak_end'] ?? null),
            ':night_start' => self::timeOrNull($data['night_start'] ?? null),
            ':night_end' => self::timeOrNull($data['night_end'] ?? null),
            ':is_active' => (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
        ]);

        return (int) self::pdo()->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public static function update(int $id, array $data): bool
    {
        $stmt = self::pdo()->prepare(
            'UPDATE pricing_rules SET branch_id=:branch_id, vehicle_type=:vehicle_type, base_fare=:base_fare, per_km=:per_km, per_hour=:per_hour, per_day=:per_day, waiting_per_hour=:waiting_per_hour, extra_km_charge=:extra_km_charge, extra_km_threshold=:extra_km_threshold, night_charge_percent=:night_charge_percent, peak_charge_percent=:peak_charge_percent, peak_start=:peak_start, peak_end=:peak_end, night_start=:night_start, night_end=:night_end, is_active=:is_active, updated_at=NOW() WHERE id=:id'
        );

        return $stmt->execute([
            ':id' => $id,
            ':branch_id' => self::nullInt($data['branch_id'] ?? null),
            ':vehicle_type' => trim((string) ($data['vehicle_type'] ?? 'car')),
            ':base_fare' => self::money($data['base_fare'] ?? 0),
            ':per_km' => self::money($data['per_km'] ?? 0),
            ':per_hour' => self::money($data['per_hour'] ?? 0),
            ':per_day' => self::money($data['per_day'] ?? 0),
            ':waiting_per_hour' => self::money($data['waiting_per_hour'] ?? 0),
            ':extra_km_charge' => self::money($data['extra_km_charge'] ?? 0),
            ':extra_km_threshold' => self::money($data['extra_km_threshold'] ?? 0),
            ':night_charge_percent' => self::money($data['night_charge_percent'] ?? 0),
            ':peak_charge_percent' => self::money($data['peak_charge_percent'] ?? 0),
            ':peak_start' => self::timeOrNull($data['peak_start'] ?? null),
            ':peak_end' => self::timeOrNull($data['peak_end'] ?? null),
            ':night_start' => self::timeOrNull($data['night_start'] ?? null),
            ':night_end' => self::timeOrNull($data['night_end'] ?? null),
            ':is_active' => (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0,
        ]);
    }

    public static function delete(int $id): bool
    {
        $stmt = self::pdo()->prepare('DELETE FROM pricing_rules WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    private static function nullInt(mixed $value): ?int
    {
        $x = (int) $value;

        return $x > 0 ? $x : null;
    }

    private static function money(mixed $v): string
    {
        if (is_string($v)) {
            $v = str_replace(',', '.', trim($v));
        }
        $f = max(0.0, (float) $v);

        return number_format($f, 2, '.', '');
    }

    private static function timeOrNull(mixed $v): ?string
    {
        $x = trim((string) $v);

        return $x !== '' ? $x : null;
    }
}
