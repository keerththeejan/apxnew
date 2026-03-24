<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\CountryCatalog;
use PDO;

final class Service extends Model
{
    /**
     * True when the `image_path` column exists.
     * If missing, attempts `ALTER TABLE` once (needs DB user with ALTER privilege) so local installs self-heal.
     */
    public static function hasImagePathColumn(): bool
    {
        try {
            $pdo = self::pdo();
            $st = $pdo->query("SHOW COLUMNS FROM `services` LIKE 'image_path'");
            if ($st !== false && $st->fetch() !== false) {
                return true;
            }
        } catch (\Throwable) {
            return false;
        }

        try {
            self::pdo()->exec('ALTER TABLE `services` ADD COLUMN `image_path` VARCHAR(500) NULL AFTER `icon`');

            return true;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (stripos($msg, 'Duplicate column') !== false || stripos($msg, '1060') !== false) {
                return true;
            }

            return false;
        }
    }

    public static function hasCountryCodeColumn(): bool
    {
        try {
            $pdo = self::pdo();
            $st = $pdo->query("SHOW COLUMNS FROM `services` LIKE 'country_code'");
            if ($st !== false && $st->fetch() !== false) {
                return true;
            }
        } catch (\Throwable) {
            return false;
        }

        try {
            self::pdo()->exec('ALTER TABLE `services` ADD COLUMN `country_code` VARCHAR(2) NULL');

            return true;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (stripos($msg, 'Duplicate column') !== false || stripos($msg, '1060') !== false) {
                return true;
            }

            return false;
        }
    }

    public static function active(): array
    {
        $stmt = self::pdo()->query('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM services WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function countAll(): int
    {
        $stmt = self::pdo()->query('SELECT COUNT(*) AS c FROM services');
        $row = $stmt->fetch();
        return (int) ($row['c'] ?? 0);
    }

    /**
     * @return list<string>
     */
    public static function distinctCountryCodes(): array
    {
        if (!self::hasCountryCodeColumn()) {
            return [];
        }
        try {
            $stmt = self::pdo()->query(
                "SELECT DISTINCT country_code FROM services WHERE country_code IS NOT NULL AND country_code != '' ORDER BY country_code ASC"
            );
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $out = [];
            foreach ($rows as $r) {
                $c = strtoupper(trim((string) ($r['country_code'] ?? '')));
                if ($c !== '') {
                    $out[] = $c;
                }
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array{rows: list<array<string,mixed>>, total: int, page: int, perPage: int, pageCount: int}
     */
    public static function paginate(string $q, int $page, int $perPage, string $country = ''): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $pdo = self::pdo();
        $where = '1=1';
        $params = [];
        $q = trim($q);
        if ($q !== '') {
            $where .= ' AND (title LIKE :q OR description LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }
        $country = strtoupper(trim($country));
        if ($country !== '' && self::hasCountryCodeColumn()) {
            $where .= ' AND country_code = :cc';
            $params[':cc'] = $country;
        }

        $cst = $pdo->prepare("SELECT COUNT(*) AS cnt FROM services WHERE {$where}");
        foreach ($params as $k => $v) {
            $cst->bindValue($k, $v);
        }
        $cst->execute();
        $total = (int) (($cst->fetch()['cnt'] ?? 0));
        $stmt = $pdo->prepare("SELECT * FROM services WHERE {$where} ORDER BY sort_order ASC, id ASC LIMIT :limit OFFSET :offset");
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

    /**
     * @param array<string, mixed> $data
     */
    private static function buildFieldMap(array $data): array
    {
        $map = [
            'icon' => (string) ($data['icon'] ?? ''),
            'title' => (string) ($data['title'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'link' => (string) ($data['link'] ?? ''),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (int) ($data['is_active'] ?? 1),
        ];
        if (self::hasImagePathColumn()) {
            $map['image_path'] = (string) ($data['image_path'] ?? '');
        }
        if (self::hasCountryCodeColumn()) {
            $cc = CountryCatalog::normalize((string) ($data['country_code'] ?? ''));
            $map['country_code'] = $cc;
        }

        return $map;
    }

    /**
     * Column order for INSERT/UPDATE (stable).
     *
     * @param array<string, mixed> $map
     *
     * @return list<string>
     */
    private static function orderedKeys(array $map): array
    {
        $order = ['icon'];
        if (array_key_exists('image_path', $map)) {
            $order[] = 'image_path';
        }
        if (array_key_exists('country_code', $map)) {
            $order[] = 'country_code';
        }
        $order = array_merge($order, ['title', 'description', 'link', 'sort_order', 'is_active']);

        return array_values(array_filter($order, static fn (string $k): bool => array_key_exists($k, $map)));
    }

    public static function create(array $data): int
    {
        $pdo = self::pdo();
        $map = self::buildFieldMap($data);
        $keys = self::orderedKeys($map);
        $cols = implode(',', $keys);
        $ph = implode(',', array_map(static fn (string $k): string => ':' . $k, $keys));
        $sql = 'INSERT INTO services (' . $cols . ') VALUES (' . $ph . ')';
        $stmt = $pdo->prepare($sql);
        foreach ($keys as $k) {
            $v = $map[$k];
            if ($k === 'sort_order' || $k === 'is_active') {
                $stmt->bindValue(':' . $k, (int) $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $k, (string) $v);
            }
        }
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $map = self::buildFieldMap($data);
        $keys = self::orderedKeys($map);
        $sets = [];
        foreach ($keys as $k) {
            $sets[] = $k . '=:' . $k;
        }
        $sql = 'UPDATE services SET ' . implode(',', $sets) . ' WHERE id=:id';
        $stmt = self::pdo()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        foreach ($keys as $k) {
            $v = $map[$k];
            if ($k === 'sort_order' || $k === 'is_active') {
                $stmt->bindValue(':' . $k, (int) $v, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $k, (string) $v);
            }
        }

        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM services WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
