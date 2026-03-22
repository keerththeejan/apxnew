<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    public function put(string $path, array $handler): void
    {
        $this->map('PUT', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->map('DELETE', $path, $handler);
    }

    private function map(string $method, string $path, array $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][] = [
            'path' => $this->normalize($path),
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        if ($method === 'POST') {
            $override = $_POST['_method'] ?? null;
            if (is_string($override) && $override !== '') {
                $override = strtoupper(trim($override));
                if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                    $method = $override;
                }
            }
        }
        $path = parse_url($uri, PHP_URL_PATH);
        $path = $this->normalize((string) $path);

        $basePath = $this->detectBasePath();
        if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
            $path = $this->normalize($path === '' ? '/' : $path);
        }

        foreach (($this->routes[$method] ?? []) as $route) {
            $params = $this->match($route['path'], $path);
            if ($params === null) {
                continue;
            }

            [$class, $action] = $route['handler'];
            $controller = new $class();
            $controller->{$action}(...array_values($params));
            return;
        }

        http_response_code(404);
        view('errors.404', [
            'path' => $path,
        ]);
    }

    private function detectBasePath(): string
    {
        $configured = (string) (env('APP_BASE_URL', '') ?? '');
        if ($configured !== '') {
            $p = parse_url($configured, PHP_URL_PATH);
            if (is_string($p) && $p !== '' && $p !== '/') {
                return $this->normalize($p);
            }
        }

        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        $dir = str_replace('\\', '/', dirname($scriptName));
        $dir = rtrim($dir, '/');
        if ($dir === '') {
            return '';
        }

        if (str_ends_with($dir, '/public')) {
            $dir = substr($dir, 0, -7);
            $dir = rtrim($dir, '/');
        }

        return $dir === '' ? '' : $this->normalize($dir);
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function match(string $routePath, string $requestPath): ?array
    {
        if ($routePath === $requestPath) {
            return [];
        }

        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return null;
        }

        $params = [];
        foreach ($routeParts as $i => $part) {
            $req = $requestParts[$i] ?? '';
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $m) === 1) {
                $params[$m[1]] = $req;
                continue;
            }
            if ($part !== $req) {
                return null;
            }
        }

        return $params;
    }
}
