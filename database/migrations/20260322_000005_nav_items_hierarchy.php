<?php

declare(strict_types=1);

return [
    'up' => function (PDO $pdo): void {
        $try = static function (string $sql) use ($pdo): void {
            try {
                $pdo->exec($sql);
            } catch (PDOException $e) {
                if (!str_contains($e->getMessage(), 'Duplicate column') && !str_contains($e->getMessage(), 'check that column/key exists')) {
                    throw $e;
                }
            }
        };

        $try('ALTER TABLE nav_items ADD COLUMN parent_id BIGINT UNSIGNED NULL AFTER id');
        $try('ALTER TABLE nav_items ADD COLUMN slug VARCHAR(190) NULL AFTER url');
        $try('ALTER TABLE nav_items ADD COLUMN icon VARCHAR(80) NULL AFTER label');
        $try('ALTER TABLE nav_items ADD COLUMN is_button TINYINT(1) NOT NULL DEFAULT 0 AFTER open_new_tab');

        $try('ALTER TABLE nav_items ADD KEY idx_nav_parent (parent_id)');

        try {
            $pdo->exec('ALTER TABLE nav_items ADD CONSTRAINT fk_nav_parent FOREIGN KEY (parent_id) REFERENCES nav_items(id) ON DELETE SET NULL ON UPDATE CASCADE');
        } catch (PDOException $e) {
            if (!str_contains($e->getMessage(), 'Duplicate foreign key') && !str_contains($e->getMessage(), 'already exists')) {
                throw $e;
            }
        }

        $btn = (int) ($pdo->query('SELECT COUNT(*) AS c FROM nav_items WHERE is_button = 1')->fetch()['c'] ?? 0);
        if ($btn === 0) {
            $orderCol = $pdo->query("SHOW COLUMNS FROM nav_items LIKE 'sort_order'")->fetch() ? 'sort_order' : 'order_index';
            $max = (int) ($pdo->query('SELECT COALESCE(MAX(`' . $orderCol . '`),0) AS m FROM nav_items')->fetch()['m'] ?? 0);
            $pdo->exec('INSERT INTO nav_items (parent_id, label, url, slug, ' . $orderCol . ', is_active, open_new_tab, icon, is_button) VALUES
(NULL, \'Apply Now\', \'/#apply\', NULL, ' . ($max + 10) . ', 1, 0, NULL, 1),
(NULL, \'Contact\', \'/contact\', NULL, ' . ($max + 11) . ', 1, 0, NULL, 1)');
        }
    },
    'down' => function (PDO $pdo): void {
        try {
            $pdo->exec('ALTER TABLE nav_items DROP FOREIGN KEY fk_nav_parent');
        } catch (PDOException $e) {
        }
        foreach (['parent_id', 'slug', 'icon', 'is_button'] as $col) {
            try {
                $pdo->exec("ALTER TABLE nav_items DROP COLUMN {$col}");
            } catch (PDOException $e) {
            }
        }
    },
];
