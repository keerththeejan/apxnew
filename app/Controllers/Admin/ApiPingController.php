<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Response;

final class ApiPingController
{
    public function ping(): void
    {
        Response::json([
            'ok' => true,
            'time' => date('c'),
        ]);
    }
}
