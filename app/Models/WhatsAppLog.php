<?php

declare(strict_types=1);

namespace App\Models;

final class WhatsAppLog extends Model
{
    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): void
    {
        self::ensureTable();
        $stmt = self::pdo()->prepare(
            'INSERT INTO whatsapp_logs (status, provider, to_phone, message_body, context_key, entity_id, http_code, response_body, created_at)
             VALUES (:status, :provider, :to_phone, :message_body, :context_key, :entity_id, :http_code, :response_body, NOW())'
        );
        $stmt->execute([
            ':status' => (string) ($data['status'] ?? 'failed'),
            ':provider' => (string) ($data['provider'] ?? ''),
            ':to_phone' => (string) ($data['to_phone'] ?? ''),
            ':message_body' => (string) ($data['message_body'] ?? ''),
            ':context_key' => (string) ($data['context_key'] ?? ''),
            ':entity_id' => isset($data['entity_id']) ? (int) $data['entity_id'] : null,
            ':http_code' => (int) ($data['http_code'] ?? 0),
            ':response_body' => (string) ($data['response_body'] ?? ''),
        ]);
    }

    public static function latest(int $limit = 100): array
    {
        self::ensureTable();
        $lim = max(1, min(500, $limit));
        $stmt = self::pdo()->prepare('SELECT * FROM whatsapp_logs ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $lim, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private static function ensureTable(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        try {
            self::pdo()->exec(
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
        } catch (\Throwable) {
        }
    }
}

