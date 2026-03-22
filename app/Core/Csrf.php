<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        if (!isset($_SESSION['_token']) || !is_string($_SESSION['_token']) || $_SESSION['_token'] === '') {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['_token'];
    }

    public static function verify(?string $token): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }

        $sessionToken = $_SESSION['_token'] ?? null;
        if (!is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}
