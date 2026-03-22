<?php

declare(strict_types=1);

function splitSqlSeed(string $sql): array
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
        $path = __DIR__ . '/../seed.sql';
        $sql = file_get_contents($path);
        if (!is_string($sql) || trim($sql) === '') {
            return;
        }
        foreach (splitSqlSeed($sql) as $stmt) {
            $pdo->exec($stmt);
        }
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DELETE FROM posts');
        $pdo->exec('DELETE FROM pages');
        $pdo->exec('DELETE FROM services');
        $pdo->exec('DELETE FROM blog_posts');
        $pdo->exec('DELETE FROM testimonials');
        $pdo->exec('DELETE FROM visas');
        $pdo->exec('DELETE FROM hotels');
        $pdo->exec('DELETE FROM flights');
        $pdo->exec('DELETE FROM destinations');
        $pdo->exec('DELETE FROM settings');
        $pdo->exec('DELETE FROM admins');
    },
];
