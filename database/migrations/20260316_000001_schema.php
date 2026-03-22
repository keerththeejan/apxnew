<?php

declare(strict_types=1);

function splitSql(string $sql): array
{
    $sql = str_replace(["\r\n", "\r"], "\n", $sql);
    $lines = explode("\n", $sql);
    $clean = [];
    foreach ($lines as $line) {
        $trim = ltrim($line);
        if (str_starts_with($trim, '--')) {
            continue;
        }
        $clean[] = $line;
    }
    $sql = implode("\n", $clean);

    $statements = [];
    $buf = '';
    $inSingle = false;
    $inDouble = false;
    $len = strlen($sql);
    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        $prev = $i > 0 ? $sql[$i - 1] : '';

        if ($ch === "'" && !$inDouble && $prev !== "\\") {
            $inSingle = !$inSingle;
        } elseif ($ch === '"' && !$inSingle && $prev !== "\\") {
            $inDouble = !$inDouble;
        }

        if ($ch === ';' && !$inSingle && !$inDouble) {
            $stmt = trim($buf);
            if ($stmt !== '') {
                $statements[] = $stmt;
            }
            $buf = '';
            continue;
        }

        $buf .= $ch;
    }

    $tail = trim($buf);
    if ($tail !== '') {
        $statements[] = $tail;
    }

    return $statements;
}

return [
    'up' => function (PDO $pdo): void {
        $path = __DIR__ . '/../schema.sql';
        $sql = file_get_contents($path);
        if (!is_string($sql) || trim($sql) === '') {
            throw new RuntimeException('schema.sql is empty');
        }
        foreach (splitSql($sql) as $stmt) {
            $pdo->exec($stmt);
        }
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        $tables = [
            'payment_transactions',
            'contact_messages',
            'applications',
            'posts',
            'pages',
            'services',
            'settings',
            'blog_posts',
            'testimonials',
            'inquiries',
            'bookings',
            'insurance_packages',
            'hotels',
            'flights',
            'visas',
            'destinations',
            'admin_notifications',
            'admins',
            'users',
        ];
        foreach ($tables as $t) {
            $pdo->exec('DROP TABLE IF EXISTS `' . $t . '`');
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    },
];
