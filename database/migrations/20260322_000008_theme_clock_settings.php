<?php

declare(strict_types=1);

return [
    'up' => function (PDO $pdo): void {
        $ins = $pdo->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (:k, :v) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );

        $rows = [
            ['theme_enabled', '1'],
            ['theme_switcher_enabled', '1'],
            ['theme_mode', 'light'],
            ['clock_enabled', '0'],
            ['clock_time_format', '24'],
        ];

        foreach ($rows as [$k, $v]) {
            $ins->execute([':k' => $k, ':v' => $v]);
        }

        // Align theme_mode with existing default_theme when present.
        try {
            $st = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'default_theme' LIMIT 1");
            $row = $st ? $st->fetch(PDO::FETCH_ASSOC) : false;
            $dt = is_array($row) ? strtolower(trim((string) ($row['value'] ?? ''))) : '';
            if ($dt === 'dark') {
                $ins->execute([':k' => 'theme_mode', ':v' => 'dark']);
            }
        } catch (\PDOException) {
        }
    },
    'down' => function (PDO $pdo): void {
        $del = $pdo->prepare('DELETE FROM settings WHERE `key` = :k');
        foreach (['theme_enabled', 'theme_switcher_enabled', 'theme_mode', 'clock_enabled', 'clock_time_format'] as $k) {
            try {
                $del->execute([':k' => $k]);
            } catch (\PDOException) {
            }
        }
    },
];
