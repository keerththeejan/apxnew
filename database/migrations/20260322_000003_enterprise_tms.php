<?php

declare(strict_types=1);

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS admin_login_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  ip VARCHAR(45) NOT NULL,
  attempted_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_email_time (email, attempted_at),
  KEY idx_ip_time (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_password_resets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_apr_token (token_hash),
  KEY idx_apr_admin (admin_id),
  CONSTRAINT fk_apr_admin FOREIGN KEY (admin_id) REFERENCES admins(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id BIGINT UNSIGNED NULL,
  action VARCHAR(160) NOT NULL,
  entity VARCHAR(120) NULL,
  entity_id BIGINT UNSIGNED NULL,
  meta_json TEXT NULL,
  ip VARCHAR(45) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_al_created (created_at),
  KEY idx_al_admin (admin_id),
  KEY idx_al_entity (entity, entity_id),
  CONSTRAINT fk_al_admin FOREIGN KEY (admin_id) REFERENCES admins(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        $seed = $pdo->prepare('INSERT INTO settings (`key`, `value`) VALUES (:k, :v) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
        $defaults = [
            ['theme_primary', '#4f8cff'],
            ['theme_accent', '#ff7a18'],
            ['theme_gradient_from', '#0f172a'],
            ['theme_gradient_to', '#1e293b'],
            ['default_theme', 'light'],
            ['default_locale', 'en'],
            ['app_timezone', 'UTC'],
            ['currency_format', 'USD %s'],
            ['login_max_attempts', '5'],
            ['login_lockout_minutes', '15'],
            ['social_links_json', ''],
        ];
        foreach ($defaults as $row) {
            $seed->execute([':k' => $row[0], ':v' => $row[1]]);
        }

        $tryAlter = static function (PDO $pdo, string $sql): void {
            try {
                $pdo->exec($sql);
            } catch (\Throwable $e) {
            }
        };
        $tryAlter($pdo, "ALTER TABLE users ADD COLUMN role VARCHAR(32) NOT NULL DEFAULT 'user' AFTER is_active");
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS activity_logs');
        $pdo->exec('DROP TABLE IF EXISTS admin_password_resets');
        $pdo->exec('DROP TABLE IF EXISTS admin_login_attempts');
    },
];
