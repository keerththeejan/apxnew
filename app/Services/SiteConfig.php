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

    public static function themeEnabled(): bool
    {
        return self::get('theme_enabled', '1') === '1';
    }

    public static function themeSwitcherEnabled(): bool
    {
        return self::get('theme_switcher_enabled', '1') === '1';
    }

    /** @return 'light'|'dark'|'auto' */
    public static function themeMode(): string
    {
        $m = strtolower(trim(self::get('theme_mode', self::get('default_theme', 'light'))));
        if ($m === 'dark') {
            return 'dark';
        }
        if ($m === 'auto') {
            return 'auto';
        }

        return 'light';
    }

    public static function clockEnabled(): bool
    {
        return self::get('clock_enabled', '0') === '1';
    }

    /** @return '12'|'24' */
    public static function clockTimeFormat(): string
    {
        $f = strtolower(trim(self::get('clock_time_format', '24')));

        return $f === '12' ? '12' : '24';
    }

    /**
     * Initial Bootstrap/html theme for SSR (auto resolves to light; client refines).
     *
     * @return 'light'|'dark'
     */
    public static function effectiveBootstrapThemeForHtml(): string
    {
        if (!self::themeEnabled()) {
            return 'light';
        }
        $mode = self::themeMode();
        if ($mode === 'dark') {
            return 'dark';
        }

        return 'light';
    }

    /**
     * JSON-friendly config for public theme + clock scripts.
     *
     * @return array<string, mixed>
     */
    public static function publicThemeClientConfig(): array
    {
        return [
            'themeEnabled' => self::themeEnabled(),
            'themeSwitcher' => self::themeSwitcherEnabled() && self::themeEnabled(),
            'themeMode' => self::themeMode(),
            'clockEnabled' => self::clockEnabled(),
            'clockFormat' => self::clockTimeFormat(),
            'timezone' => self::get('app_timezone', 'UTC'),
            'storageKey' => 'tms_theme',
        ];
    }
}
