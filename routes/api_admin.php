<?php

declare(strict_types=1);

use App\Controllers\Admin\ApiPingController;

$router->get('/api/admin/ping', [ApiPingController::class, 'ping']);
