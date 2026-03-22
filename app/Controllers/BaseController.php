<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;

abstract class BaseController
{
    protected function redirect(string $path): void
    {
        Response::redirect($path);
    }
}
