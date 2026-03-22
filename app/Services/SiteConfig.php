<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

/**
 * Cached key/value site configuration from the `settings` table.
 */
final class SiteConfig
{
    /** @var array<string, string>|null */
    private static ?array $cache = null;

    public static function forget(): void
    {
        self::$cache = null;
    }

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        if (self::$cache === null) {
            try {
                self::$cache = Setting::allKeyed();
            } catch (\Throwable $e) {
                self::$cache = [];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, string $default = ''): string
    {
        $v = self::all()[$key] ?? null;
        return $v !== null && $v !== '' ? (string) $v : $default;
    }

    /**
     * @return list<array{label:string,url:string,icon?:string}>
     */
    public static function socialLinks(): array
    {
        $raw = trim(self::get('social_links_json', ''));
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $out = [];
                foreach ($decoded as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $label = trim((string) ($row['label'] ?? ''));
                    $url = trim((string) ($row['url'] ?? ''));
                    if ($label === '' || $url === '') {
                        continue;
                    }
                    $out[] = [
                        'label' => $label,
                        'url' => $url,
                        'icon' => trim((string) ($row['icon'] ?? '')),
                    ];
                }
                if ($out !== []) {
                    return $out;
                }
            }
        }

        return [
            ['label' => 'Facebook', 'url' => self::get('social_facebook', '#'), 'icon' => 'facebook'],
            ['label' => 'Instagram', 'url' => self::get('social_instagram', '#'), 'icon' => 'instagram'],
            ['label' => 'YouTube', 'url' => self::get('social_youtube', '#'), 'icon' => 'youtube'],
            ['label' => 'TikTok', 'url' => self::get('social_tiktok', '#'), 'icon' => 'tiktok'],
        ];
    }

    public static function themeCssVars(): string
    {
        $primary = self::get('theme_primary', '#4f8cff');
        $accent = self::get('theme_accent', '#ff7a18');
        $g1 = self::get('theme_gradient_from', '#0f172a');
        $g2 = self::get('theme_gradient_to', '#1e293b');
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $primary)) {
            $primary = '#4f8cff';
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $accent)) {
            $accent = '#ff7a18';
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $g1)) {
            $g1 = '#0f172a';
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $g2)) {
            $g2 = '#1e293b';
        }

        return ':root{'
            . '--tms-primary:' . $primary . ';'
            . '--tms-accent:' . $accent . ';'
            . '--tms-grad-from:' . $g1 . ';'
            . '--tms-grad-to:' . $g2 . ';'
            . '}';
    }
}
