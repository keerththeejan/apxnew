<?php

declare(strict_types=1);

return [
    'up' => function (PDO $pdo): void {
        try {
            $pdo->exec('ALTER TABLE services ADD COLUMN image_path VARCHAR(500) NULL AFTER icon');
        } catch (\PDOException $e) {
            if (stripos($e->getMessage(), 'Duplicate column') === false) {
                throw $e;
            }
        }
    },
    'down' => function (PDO $pdo): void {
        try {
            $pdo->exec('ALTER TABLE services DROP COLUMN image_path');
        } catch (\PDOException) {
        }
    },
];
