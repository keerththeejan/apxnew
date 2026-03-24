<?php

declare(strict_types=1);

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS quote_routes (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                slug VARCHAR(64) NOT NULL,
                label VARCHAR(190) NOT NULL,
                country VARCHAR(120) NOT NULL,
                service VARCHAR(120) NOT NULL,
                price_per_kg DECIMAL(12,2) NOT NULL DEFAULT 0,
                sort_order INT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_quote_routes_slug (slug),
                KEY idx_quote_routes_sort (sort_order),
                KEY idx_quote_routes_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $cnt = (int) ($pdo->query('SELECT COUNT(*) AS c FROM quote_routes')->fetch()['c'] ?? 0);
        if ($cnt === 0) {
            $pdo->exec(
                "INSERT INTO quote_routes (slug, label, country, service, price_per_kg, sort_order, is_active) VALUES
                ('usa-dhl','USA – DHL','USA','DHL',500.00,1,1),
                ('uk-fedex','United Kingdom – FedEx','United Kingdom','FedEx',420.00,2,1),
                ('ae-aramex','UAE – Aramex','UAE','Aramex',380.00,3,1),
                ('lk-local','Sri Lanka – Local','Sri Lanka','Local',120.00,4,1)"
            );
        }
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS quote_routes');
    },
];
