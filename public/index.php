<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;

$router = new Router();

require __DIR__ . '/../routes/web.php';
require __DIR__ . '/../routes/admin.php';
require __DIR__ . '/../routes/api_admin.php';

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
