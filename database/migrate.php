<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Db;

$cmd = $argv[1] ?? 'up';
$cmd = strtolower((string) $cmd);

$pdo = Db::pdo();

$pdo->exec('CREATE TABLE IF NOT EXISTS migrations (\n  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,\n  name VARCHAR(190) NOT NULL,\n  batch INT NOT NULL,\n  applied_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,\n  PRIMARY KEY (id),\n  UNIQUE KEY uq_migrations_name (name)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

function readApplied(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT name FROM migrations ORDER BY id ASC');
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    $out = [];
    foreach ($rows as $r) {
        $out[(string) ($r['name'] ?? '')] = true;
    }
    return $out;
}

function nextBatch(PDO $pdo): int
{
    $stmt = $pdo->query('SELECT MAX(batch) AS b FROM migrations');
    $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    $b = (int) (($row['b'] ?? 0));
    return $b + 1;
}

function loadMigrations(string $dir): array
{
    if (!is_dir($dir)) {
        return [];
    }
    $files = glob($dir . '/*.php');
    if (!is_array($files)) {
        return [];
    }
    sort($files);
    return $files;
}

function runMigration(PDO $pdo, string $filePath, string $direction): void
{
    $migration = require $filePath;
    if (!is_array($migration)) {
        throw new RuntimeException('Invalid migration file: ' . $filePath);
    }
    $fn = $migration[$direction] ?? null;
    if (!is_callable($fn)) {
        throw new RuntimeException('Migration missing ' . $direction . ': ' . $filePath);
    }
    $fn($pdo);
}

$migrationsDir = __DIR__ . '/migrations';
$files = loadMigrations($migrationsDir);

if ($cmd === 'status') {
    $applied = readApplied($pdo);
    foreach ($files as $f) {
        $name = basename($f);
        $mark = isset($applied[$name]) ? 'Y' : 'N';
        echo $mark . ' ' . $name . PHP_EOL;
    }
    exit;
}

if ($cmd === 'down') {
    $stmt = $pdo->query('SELECT batch FROM migrations ORDER BY batch DESC LIMIT 1');
    $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    $batch = (int) (($row['batch'] ?? 0));
    if ($batch < 1) {
        echo "No migrations to rollback" . PHP_EOL;
        exit;
    }

    $stmt = $pdo->prepare('SELECT name FROM migrations WHERE batch = :b ORDER BY id DESC');
    $stmt->bindValue(':b', $batch, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $map = [];
    foreach ($files as $f) {
        $map[basename($f)] = $f;
    }

    $pdo->beginTransaction();
    try {
        foreach ($rows as $r) {
            $name = (string) ($r['name'] ?? '');
            $path = $map[$name] ?? null;
            if (!$path) {
                continue;
            }
            runMigration($pdo, $path, 'down');
            $del = $pdo->prepare('DELETE FROM migrations WHERE name = :n');
            $del->bindValue(':n', $name);
            $del->execute();
            echo 'Rolled back: ' . $name . PHP_EOL;
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    exit;
}

$applied = readApplied($pdo);
$batch = nextBatch($pdo);

$pdo->beginTransaction();
try {
    foreach ($files as $f) {
        $name = basename($f);
        if (isset($applied[$name])) {
            continue;
        }

        runMigration($pdo, $f, 'up');

        $ins = $pdo->prepare('INSERT INTO migrations (name, batch) VALUES (:n, :b)');
        $ins->bindValue(':n', $name);
        $ins->bindValue(':b', $batch, PDO::PARAM_INT);
        $ins->execute();

        echo 'Applied: ' . $name . PHP_EOL;
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
