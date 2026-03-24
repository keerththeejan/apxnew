<?php

declare(strict_types=1);

namespace App\Services;

/**
 * ISO 3166-1 alpha-2 list from `public/data/countries.json`.
 */
final class CountryCatalog
{
    /** @var list<array{code:string,name:string}>|null */
    private static ?array $rows = null;

    /** @var array<string, string>|null code => name */
    private static ?array $byCode = null;

    /**
     * @return list<array{code:string,name:string}>
     */
    public static function all(): array
    {
        if (self::$rows !== null) {
            return self::$rows;
        }

        $path = dirname(__DIR__, 2) . '/public/data/countries.json';
        if (!is_file($path)) {
            self::$rows = [];

            return self::$rows;
        }

        $raw = file_get_contents($path);
        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            self::$rows = [];

            return self::$rows;
        }

        $out = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }
            $code = strtoupper(trim((string) ($row['code'] ?? '')));
            $name = trim((string) ($row['name'] ?? ''));
            if ($code !== '' && $name !== '') {
                $out[] = ['code' => $code, 'name' => $name];
            }
        }

        self::$rows = $out;

        return self::$rows;
    }

    public static function nameForCode(string $code): string
    {
        $code = strtoupper(trim($code));
        if (self::$byCode === null) {
            self::$byCode = [];
            foreach (self::all() as $r) {
                self::$byCode[$r['code']] = $r['name'];
            }
        }

        return self::$byCode[$code] ?? '';
    }

    public static function normalize(?string $code): string
    {
        $c = strtoupper(trim((string) $code));
        if (!preg_match('/^[A-Z]{2}$/', $c)) {
            return '';
        }

        return self::nameForCode($c) !== '' ? $c : '';
    }

    /** flagcdn.com — use lowercase ISO code in path */
    public static function flagUrl(string $code, int $width = 40): string
    {
        $c = strtolower(trim($code));

        return 'https://flagcdn.com/w' . max(16, min(80, $width)) . '/' . $c . '.png';
    }
}
