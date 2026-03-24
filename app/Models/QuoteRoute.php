<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class QuoteRoute extends Model
{
    public static function schemaReady(): bool
    {
        return self::safe(function (): bool {
            self::pdo()->query('SELECT 1 FROM quote_routes LIMIT 1');

            return true;
        }, false);
    }

    /** Active routes for the public quote widget (ordered). */
    /** @return list<array<string, mixed>> */
    public static function activeOrdered(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query(
                'SELECT slug, label, country, service, price_per_kg FROM quote_routes WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
            );
            $rows = $stmt->fetchAll() ?: [];
            foreach ($rows as &$r) {
                $r['price_per_kg'] = (string) ($r['price_per_kg'] ?? '0');
            }
            unset($r);

            return $rows;
        }, []);
    }

    /** @return list<array<string, mixed>> */
    public static function adminAll(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM quote_routes ORDER BY sort_order ASC, id ASC');

            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function findById(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        return self::safe(function () use ($id): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM quote_routes WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            return $row === false ? null : $row;
        }, null);
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $slug = trim($slug);
        if ($slug === '') {
            return true;
        }

        return self::safe(function () use ($slug, $exceptId): bool {
            $sql = 'SELECT COUNT(*) AS c FROM quote_routes WHERE slug = :s';
            $params = [':s' => $slug];
            if ($exceptId !== null && $exceptId > 0) {
                $sql .= ' AND id <> :id';
                $params[':id'] = $exceptId;
            }
            $stmt = self::pdo()->prepare($sql);
            $stmt->execute($params);
            $c = (int) (($stmt->fetch()['c'] ?? 0));

            return $c > 0;
        }, false);
    }

    public static function generateSlug(string $country, string $service, ?int $exceptId = null): string
    {
        $base = strtolower(trim($country) . '-' . trim($service));
        $base = preg_replace('/[^a-z0-9]+/', '-', $base) ?? '';
        $base = trim((string) $base, '-');
        if ($base === '') {
            $base = 'route';
        }
        $slug = $base;
        $n = 2;
        while (self::slugExists($slug, $exceptId)) {
            $slug = $base . '-' . $n;
            $n++;
        }

        return $slug;
    }

    /** @param array{label:string,country:string,service:string,price_per_kg:float|string,slug?:string,sort_order:int,is_active:int} $data */
    public static function create(array $data): int
    {
        $country = trim((string) ($data['country'] ?? ''));
        $service = trim((string) ($data['service'] ?? ''));
        $label = trim((string) ($data['label'] ?? ''));
        if ($label === '') {
            $label = $country !== '' && $service !== '' ? $country . ' – ' . $service : 'Route';
        }
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '' || self::slugExists($slug, null)) {
            $slug = self::generateSlug($country, $service, null);
        }
        $price = self::normalizePrice($data['price_per_kg'] ?? 0);
        $sort = (int) ($data['sort_order'] ?? 0);
        $active = (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0;

        $pdo = self::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO quote_routes (slug, label, country, service, price_per_kg, sort_order, is_active) VALUES (:slug,:label,:country,:service,:price,:sort,:active)'
        );
        $stmt->execute([
            ':slug' => $slug,
            ':label' => $label,
            ':country' => $country,
            ':service' => $service,
            ':price' => $price,
            ':sort' => $sort,
            ':active' => $active,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** @param array{label:string,country:string,service:string,price_per_kg:float|string,slug?:string,sort_order:int,is_active:int} $data */
    public static function update(int $id, array $data): bool
    {
        if ($id < 1) {
            return false;
        }
        $country = trim((string) ($data['country'] ?? ''));
        $service = trim((string) ($data['service'] ?? ''));
        $label = trim((string) ($data['label'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '' || self::slugExists($slug, $id)) {
            $slug = self::generateSlug($country, $service, $id);
        }
        $price = self::normalizePrice($data['price_per_kg'] ?? 0);
        $sort = (int) ($data['sort_order'] ?? 0);
        $active = (int) ($data['is_active'] ?? 1) === 1 ? 1 : 0;

        $stmt = self::pdo()->prepare(
            'UPDATE quote_routes SET slug=:slug, label=:label, country=:country, service=:service, price_per_kg=:price, sort_order=:sort, is_active=:active, updated_at=NOW() WHERE id=:id'
        );

        return $stmt->execute([
            ':id' => $id,
            ':slug' => $slug,
            ':label' => $label,
            ':country' => $country,
            ':service' => $service,
            ':price' => $price,
            ':sort' => $sort,
            ':active' => $active,
        ]);
    }

    public static function delete(int $id): bool
    {
        if ($id < 1) {
            return false;
        }
        $stmt = self::pdo()->prepare('DELETE FROM quote_routes WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }

    /** @param mixed $v */
    private static function normalizePrice(mixed $v): string
    {
        if (is_string($v)) {
            $v = str_replace(',', '.', trim($v));
        }
        $f = (float) $v;
        if ($f < 0) {
            $f = 0.0;
        }

        return number_format($f, 2, '.', '');
    }
}
