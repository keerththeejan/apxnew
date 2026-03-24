<?php

declare(strict_types=1);

return [
    'up' => function (PDO $pdo): void {
        try {
            $pdo->exec('ALTER TABLE services ADD COLUMN country_code VARCHAR(2) NULL');
        } catch (\PDOException $e) {
            if (stripos($e->getMessage(), 'Duplicate column') === false && stripos($e->getMessage(), '1060') === false) {
                throw $e;
            }
        }
    },
    'down' => function (PDO $pdo): void {
        try {
            $pdo->exec('ALTER TABLE services DROP COLUMN country_code');
        } catch (\PDOException) {
        }
    },
];
