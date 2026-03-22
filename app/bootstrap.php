<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/Core/Autoloader.php';

App\Core\Autoloader::register(__DIR__ . '/..');

require __DIR__ . '/../config/helpers.php';

App\Core\Env::load(__DIR__ . '/../.env');

$timezone = env('APP_TIMEZONE', 'UTC');
date_default_timezone_set($timezone);
