<?php

declare(strict_types=1);

return [
    'up' => function (PDO $pdo): void {
        try {
            $stmt = $pdo->query('SELECT 1 FROM nav_items LIMIT 1');
            if ($stmt === false || $stmt->fetch() === false) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $check = $pdo->query("SELECT COUNT(*) AS c FROM nav_items WHERE url IN ('/quote','/quote/')")->fetch();
        if ($check !== false && (int) ($check['c'] ?? 0) > 0) {
            return;
        }

        $orderCol = 'order_index';
        try {
            $r = $pdo->query("SHOW COLUMNS FROM nav_items LIKE 'order_index'")->fetch();
            if (!$r) {
                $orderCol = 'sort_order';
            }
        } catch (\Throwable) {
            $orderCol = 'sort_order';
        }

        $maxStmt = $pdo->query('SELECT COALESCE(MAX(`' . $orderCol . '`), 0) AS m FROM nav_items');
        $next = (int) (($maxStmt ? $maxStmt->fetch()['m'] : 0) ?? 0) + 1;

        $hasParent = false;
        $hasSlug = false;
        $hasIcon = false;
        $hasButton = false;
        try {
            $hasParent = $pdo->query("SHOW COLUMNS FROM nav_items LIKE 'parent_id'")->fetch() !== false;
            $hasSlug = $pdo->query("SHOW COLUMNS FROM nav_items LIKE 'slug'")->fetch() !== false;
            $hasIcon = $pdo->query("SHOW COLUMNS FROM nav_items LIKE 'icon'")->fetch() !== false;
            $hasButton = $pdo->query("SHOW COLUMNS FROM nav_items LIKE 'is_button'")->fetch() !== false;
        } catch (\Throwable) {
        }

        $label = $pdo->quote('Get a quote');
        $url = $pdo->quote('/quote');
        $oi = (int) $next;

        if ($hasParent && $hasSlug && $hasIcon && $hasButton) {
            $slug = $pdo->quote('quote');
            $pdo->exec(
                "INSERT INTO nav_items (parent_id, label, url, slug, `{$orderCol}`, is_active, open_new_tab, icon, is_button) VALUES "
                . "(NULL, {$label}, {$url}, {$slug}, {$oi}, 1, 0, NULL, 0)"
            );
        } elseif ($hasParent && $hasButton) {
            $pdo->exec(
                "INSERT INTO nav_items (parent_id, label, url, `{$orderCol}`, is_active, open_new_tab, is_button) VALUES "
                . "(NULL, {$label}, {$url}, {$oi}, 1, 0, 0)"
            );
        } else {
            $pdo->exec(
                "INSERT INTO nav_items (label, url, `{$orderCol}`, is_active, open_new_tab) VALUES ({$label}, {$url}, {$oi}, 1, 0)"
            );
        }
    },
    'down' => function (PDO $pdo): void {
        try {
            $pdo->exec("DELETE FROM nav_items WHERE url IN ('/quote','/quote/') LIMIT 5");
        } catch (\Throwable) {
        }
    },
];
