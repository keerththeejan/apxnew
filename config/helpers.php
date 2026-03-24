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
