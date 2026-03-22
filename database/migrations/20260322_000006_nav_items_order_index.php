<?php

declare(strict_types=1);

return [
    'up' => function (PDO $pdo): void {
        $hasSort = $pdo->query("SHOW COLUMNS FROM nav_items LIKE 'sort_order'")->fetch();
        if ($hasSort) {
            try {
                $pdo->exec('ALTER TABLE nav_items CHANGE COLUMN sort_order order_index INT NOT NULL DEFAULT 0');
            } catch (PDOException $e) {
                if (!str_contains($e->getMessage(), 'check that column exists') && !str_contains($e->getMessage(), 'Duplicate')) {
                    throw $e;
                }
            }
        }
        try {
            $pdo->exec('ALTER TABLE nav_items RENAME INDEX idx_nav_sort TO idx_nav_order_index');
        } catch (PDOException $e) {
        }
    },
    'down' => function (PDO $pdo): void {
        $hasOi = $pdo->query("SHOW COLUMNS FROM nav_items LIKE 'order_index'")->fetch();
        if ($hasOi) {
            try {
                $pdo->exec('ALTER TABLE nav_items CHANGE COLUMN order_index sort_order INT NOT NULL DEFAULT 0');
            } catch (PDOException $e) {
            }
        }
        try {
            $pdo->exec('ALTER TABLE nav_items RENAME INDEX idx_nav_order_index TO idx_nav_sort');
        } catch (PDOException $e) {
        }
    },
];
