<?php

declare(strict_types=1);

return new class {
    public function up(\PDO $pdo): void
    {
        try {
            $pdo->exec('ALTER TABLE applications ADD COLUMN whatsapp_number VARCHAR(30) NULL AFTER phone');
        } catch (\Throwable $e) {
        }
        try {
            $pdo->exec('ALTER TABLE applications ADD COLUMN country_code VARCHAR(2) NULL AFTER whatsapp_number');
        } catch (\Throwable $e) {
        }
        try {
            $pdo->exec('ALTER TABLE bookings ADD COLUMN whatsapp_number VARCHAR(30) NULL AFTER phone');
        } catch (\Throwable $e) {
        }
        try {
            $pdo->exec('ALTER TABLE bookings ADD COLUMN country_code VARCHAR(2) NULL AFTER whatsapp_number');
        } catch (\Throwable $e) {
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS whatsapp_logs (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                status VARCHAR(20) NOT NULL,
                provider VARCHAR(40) NOT NULL,
                to_phone VARCHAR(30) NOT NULL,
                message_body TEXT NOT NULL,
                context_key VARCHAR(80) NULL,
                entity_id BIGINT NULL,
                http_code INT NOT NULL DEFAULT 0,
                response_body TEXT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_whatsapp_logs_status (status),
                KEY idx_whatsapp_logs_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        try {
            $pdo->exec('ALTER TABLE applications DROP COLUMN country_code');
        } catch (\Throwable $e) {
        }
        try {
            $pdo->exec('ALTER TABLE applications DROP COLUMN whatsapp_number');
        } catch (\Throwable $e) {
        }
        try {
            $pdo->exec('ALTER TABLE bookings DROP COLUMN country_code');
        } catch (\Throwable $e) {
        }
        try {
            $pdo->exec('ALTER TABLE bookings DROP COLUMN whatsapp_number');
        } catch (\Throwable $e) {
        }
        $pdo->exec('DROP TABLE IF EXISTS whatsapp_logs');
    }
};

