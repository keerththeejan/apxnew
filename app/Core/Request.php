<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public static function input(string $key, $default = null)
    {
        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }
        if (array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }
        return $default;
    }

    public static function post(string $key, $default = null)
    {
        if (array_key_exists($key, $_POST)) {
            return $_POST[$key];
        }
        return $default;
    }

    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }
        return $default;
    }

    public static function header(string $key, $default = null)
    {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        if (array_key_exists($serverKey, $_SERVER)) {
            return $_SERVER[$serverKey];
        }
        if (strtolower($key) === 'content-type' && array_key_exists('CONTENT_TYPE', $_SERVER)) {
            return $_SERVER['CONTENT_TYPE'];
        }
        return $default;
    }

    public static function isJson(): bool
    {
        $ct = (string) (self::header('Content-Type', '') ?? '');
        return stripos($ct, 'application/json') !== false;
    }

    public static function json(): array
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
