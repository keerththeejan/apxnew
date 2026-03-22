<?php

declare(strict_types=1);

/**
 * Idempotent: adds nav_items hierarchy columns (parent_id, slug, icon, is_button)
 * if they are missing. Fixes SQLSTATE[42S22] Unknown column 'parent_id'.
 *
 * Run: php database/repair_nav_items.php
 * (WAMP example: C:\wamp64\bin\php\php8.3.28\php.exe database\repair_nav_items.php)
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Db;

$pdo = Db::pdo();

function tableExists(PDO $pdo, string $table): bool
{
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t'
    );
    $st->execute([':t' => $table]);

    return (int) $st->fetchColumn() > 0;
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c'
    );
    $st->execute([':t' => $table, ':c' => $column]);

    return (int) $st->fetchColumn() > 0;
}

function indexExists(PDO $pdo, string $table, string $indexName): bool
{
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND INDEX_NAME = :i'
    );
    $st->execute([':t' => $table, ':i' => $indexName]);

    return (int) $st->fetchColumn() > 0;
}

function fkExists(PDO $pdo, string $table, string $constraintName): bool
{
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t
           AND CONSTRAINT_TYPE = \'FOREIGN KEY\' AND CONSTRAINT_NAME = :n'
    );
    $st->execute([':t' => $table, ':n' => $constraintName]);

    return (int) $st->fetchColumn() > 0;
}

if (!tableExists($pdo, 'nav_items')) {
    fwrite(STDERR, "Table nav_items does not exist. Run: php database/migrate.php\n");
    exit(1);
}

// DDL (ALTER) auto-commits in MySQL — do not wrap in a transaction.
if (!columnExists($pdo, 'nav_items', 'parent_id')) {
    $pdo->exec('ALTER TABLE nav_items ADD COLUMN parent_id BIGINT UNSIGNED NULL AFTER id');
    echo "Added column parent_id\n";
}
if (!columnExists($pdo, 'nav_items', 'slug')) {
    $pdo->exec('ALTER TABLE nav_items ADD COLUMN slug VARCHAR(190) NULL AFTER url');
    echo "Added column slug\n";
}
if (!columnExists($pdo, 'nav_items', 'icon')) {
    $pdo->exec('ALTER TABLE nav_items ADD COLUMN icon VARCHAR(80) NULL AFTER label');
    echo "Added column icon\n";
}
if (!columnExists($pdo, 'nav_items', 'is_button')) {
    $pdo->exec('ALTER TABLE nav_items ADD COLUMN is_button TINYINT(1) NOT NULL DEFAULT 0 AFTER open_new_tab');
    echo "Added column is_button\n";
}

if (!indexExists($pdo, 'nav_items', 'idx_nav_parent')) {
    $pdo->exec('ALTER TABLE nav_items ADD KEY idx_nav_parent (parent_id)');
    echo "Added index idx_nav_parent\n";
}

if (!fkExists($pdo, 'nav_items', 'fk_nav_parent')) {
    $pdo->exec(
        'ALTER TABLE nav_items ADD CONSTRAINT fk_nav_parent FOREIGN KEY (parent_id) REFERENCES nav_items(id) ON DELETE SET NULL ON UPDATE CASCADE'
    );
    echo "Added foreign key fk_nav_parent\n";
}

echo "nav_items repair finished OK.\n";
