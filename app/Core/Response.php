<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function redirect(string $path): void
    {
        header('Location: ' . base_url($path), true, 302);
        exit;
    }

    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
