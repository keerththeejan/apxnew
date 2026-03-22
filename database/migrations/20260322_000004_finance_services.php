<?php

declare(strict_types=1);

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS finance_services (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  status ENUM('draft','active') NOT NULL DEFAULT 'draft',
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_fs_status (status),
  KEY idx_fs_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);

        $cnt = (int) ($pdo->query('SELECT COUNT(*) AS c FROM finance_services')->fetch()['c'] ?? 0);
        if ($cnt === 0) {
            $pdo->exec("INSERT INTO finance_services (title, description, status, sort_order) VALUES
('Installment Plan','Split payments for selected travel packages.','active',1),
('Pay Later','Reserve now and pay closer to departure where eligible.','draft',2)");
        }
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS finance_services');
    },
];
