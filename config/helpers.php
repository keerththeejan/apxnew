<?php

declare(strict_types=1);

use App\Core\Csrf;

function env(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return (string) $value;
}

function base_url(string $path = ''): string
{
    $configured = rtrim((string) (env('APP_BASE_URL', '') ?? ''), '/');
    $path = '/' . ltrim($path, '/');
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));

    $isLocalHost = static function (string $h): bool {
        $h = strtolower(trim($h));
        if ($h === '') {
            return false;
        }
        if ($h === 'localhost' || $h === '127.0.0.1' || $h === '::1') {
            return true;
        }
        return str_ends_with($h, '.test') || str_ends_with($h, '.local');
    };

    if ($configured !== '') {
        $cfgHost = strtolower((string) (parse_url($configured, PHP_URL_HOST) ?? ''));
        // Ignore localhost APP_BASE_URL on production domains.
        if (!($cfgHost !== '' && $isLocalHost($cfgHost) && !$isLocalHost($host))) {
            return $configured . $path;
        }
    }

    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $dir = str_replace('\\', '/', dirname($scriptName));
    $dir = rtrim($dir, '/');
    if (str_ends_with($dir, '/public')) {
        $dir = substr($dir, 0, -7);
        $dir = rtrim($dir, '/');
    }

    $basePath = $dir === '' ? '' : $dir;
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    if ((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
        $scheme = 'https';
    }
    if ($host !== '') {
        return $scheme . '://' . $host . $basePath . $path;
    }

    return $basePath . $path;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function view(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);

    $viewPath = __DIR__ . '/../app/Views/' . str_replace('.', '/', $view) . '.php';
    if (!is_file($viewPath)) {
        http_response_code(500);
        echo 'View not found';
        return;
    }

    require $viewPath;
}

/**
 * Public pages: merge shared layout data (navbar, footer, settings).
 *
 * @param array<string, mixed> $data
 */
function view_public(string $view, array $data = []): void
{
    $shared = \App\Services\PublicLayout::shared();
    view($view, array_merge($shared, $data));
}

function resolve_public_href(string $url): string
{
    $url = trim($url);
    if ($url === '' || $url === '#') {
        return '#';
    }
    if (preg_match('#^https?://#i', $url) === 1) {
        return $url;
    }
    $path = '/' . ltrim($url, '/');
    return base_url($path);
}

function csrf_field(): string
{
    $token = Csrf::token();
    return '<input type="hidden" name="_token" value="' . e($token) . '">';
}

/**
 * Cache-busted public asset URL (filemtime).
 */
function asset_url(string $path): string
{
    $path = '/' . ltrim($path, '/');
    $full = __DIR__ . '/..' . $path;
    $v = is_file($full) ? (string) filemtime($full) : (string) time();

    return base_url($path) . '?v=' . $v;
}

/**
 * Bootstrap Icons class for a social link label/icon key.
 */
function apx_social_bi_class(string $label, string $icon = ''): string
{
    $icon = strtolower(trim($icon));
    $label = strtolower(trim($label));
    $map = [
        'facebook' => 'bi-facebook',
        'instagram' => 'bi-instagram',
        'youtube' => 'bi-youtube',
        'tiktok' => 'bi-tiktok',
        'twitter' => 'bi-twitter-x',
        'x' => 'bi-twitter-x',
        'linkedin' => 'bi-linkedin',
        'whatsapp' => 'bi-whatsapp',
    ];
    if ($icon !== '' && isset($map[$icon])) {
        return $map[$icon];
    }
    foreach ($map as $key => $cls) {
        if (str_contains($label, $key)) {
            return $cls;
        }
    }

    return 'bi-link-45deg';
}

/**
 * Presentation-only hero title (does not alter stored CMS data).
 */
function apx_hero_display_title(string $title, array $settings = []): string
{
    $t = trim($title);
    if ($t === '' || preg_match('/^hello\s+apx$/i', $t) === 1) {
        return 'Travel the World with Confidence';
    }
    if (strcasecmp($t, 'home') === 0) {
        return 'Travel the World with Confidence';
    }

    return $t;
}

/**
 * Presentation-only hero subtitle with professional fallback.
 */
function apx_hero_display_subtitle(string $subtitle, array $settings = []): string
{
    $s = trim($subtitle);
    if ($s !== '') {
        return $s;
    }
    $fromSettings = trim((string) ($settings['home_hero_subtitle'] ?? ''));
    if ($fromSettings !== '') {
        return $fromSettings;
    }

    return 'Visas, flights, hotels, insurance and vehicle booking — expertly managed for a seamless journey.';
}
