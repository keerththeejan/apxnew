<?php

declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    public static function register(string $projectRoot): void
    {
        spl_autoload_register(function (string $class) use ($projectRoot): void {
            $prefix = 'App\\';
            if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

            $paths = [
                $projectRoot . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $relativePath,
            ];

            foreach ($paths as $path) {
                if (is_file($path)) {
                    require $path;
                    return;
                }
            }
        });
    }
}
