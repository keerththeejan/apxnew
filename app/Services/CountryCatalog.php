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

    /** flagcdn.com only serves fixed widths; arbitrary values (e.g. w120) return 404. */
    private const FLAGCDN_WIDTHS = [20, 40, 80, 160, 320, 640, 1280, 2560];

    private static function snapFlagcdnWidth(int $width): int
    {
        $w = max(20, min(2560, $width));
        $best = self::FLAGCDN_WIDTHS[0];
        $bestDiff = PHP_INT_MAX;
        foreach (self::FLAGCDN_WIDTHS as $a) {
            $d = abs($a - $w);
            if ($d < $bestDiff || ($d === $bestDiff && $a > $best)) {
                $bestDiff = $d;
                $best = $a;
            }
        }

        return $best;
    }

    /** flagcdn.com — use lowercase ISO code in path; $width is snapped to a supported size */
    public static function flagUrl(string $code, int $width = 80): string
    {
        $c = strtolower(trim($code));
        $snap = self::snapFlagcdnWidth(max(20, min(640, $width)));

        return 'https://flagcdn.com/w' . $snap . '/' . $c . '.png';
    }

    /**
     * PNG URLs for a crisp img at roughly $cssWidth CSS pixels (1x + 2x sources).
     *
     * @return array{src:string,src2x:string}
     */
    public static function flagImgSrcPair(string $code, int $cssWidth): array
    {
        $w1 = max(80, min(640, (int) round($cssWidth * 1.6)));
        $w2 = max(160, min(640, (int) round($cssWidth * 3.2)));

        return [
            'src' => self::flagUrl($code, $w1),
            'src2x' => self::flagUrl($code, $w2),
        ];
    }
}
