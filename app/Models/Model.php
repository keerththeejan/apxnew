<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;
use PDO;

abstract class Model
{
    protected static function pdo(): PDO
    {
        return Db::pdo();
    }

    /**
     * Run a DB read when optional CMS tables may not exist yet (migration not applied).
     *
     * @template T
     * @param callable(): T $fn
     * @param T $default
     * @return T
     */
    protected static function safe(callable $fn, mixed $default = null): mixed
    {
        try {
            return $fn();
        } catch (\PDOException $e) {
            $m = $e->getMessage();
            if (str_contains($m, '42S02') || str_contains($m, "doesn't exist")) {
                return $default;
            }
            throw $e;
        }
    }
}
