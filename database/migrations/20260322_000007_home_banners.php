<?php

declare(strict_types=1);

return [
    'up' => function (PDO $pdo): void {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS home_banners (
              id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              title VARCHAR(220) NOT NULL DEFAULT \'\',
              subtitle VARCHAR(500) NULL,
              image_path VARCHAR(500) NULL,
              show_image TINYINT(1) NOT NULL DEFAULT 1,
              button1_text VARCHAR(120) NOT NULL DEFAULT \'\',
              button1_link VARCHAR(500) NOT NULL DEFAULT \'\',
              button2_text VARCHAR(120) NOT NULL DEFAULT \'\',
              button2_link VARCHAR(500) NOT NULL DEFAULT \'\',
              order_index INT NOT NULL DEFAULT 0,
              is_active TINYINT(1) NOT NULL DEFAULT 1,
              created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
              updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (id),
              KEY idx_home_banners_order (order_index),
              KEY idx_home_banners_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $cnt = (int) ($pdo->query('SELECT COUNT(*) AS c FROM home_banners')->fetch()['c'] ?? 0);
        if ($cnt > 0) {
            return;
        }

        $hero = false;
        try {
            $stmt = $pdo->query("SELECT * FROM hero_sections WHERE page_key = 'home' LIMIT 1");
            $hero = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
        } catch (\PDOException) {
            $hero = false;
        }
        if (is_array($hero)) {
            $ins = $pdo->prepare(
                'INSERT INTO home_banners (title, subtitle, image_path, show_image, button1_text, button1_link, button2_text, button2_link, order_index, is_active)
                 VALUES (:title,:subtitle,:img,:showimg,:b1t,:b1u,:b2t,:b2u,:oi,:active)'
            );
            $ins->execute([
                ':title' => (string) ($hero['title'] ?? ''),
                ':subtitle' => (string) ($hero['subtitle'] ?? ''),
                ':img' => trim((string) ($hero['bg_image_path'] ?? '')) !== '' ? (string) $hero['bg_image_path'] : null,
                ':showimg' => trim((string) ($hero['bg_image_path'] ?? '')) !== '' ? 1 : 0,
                ':b1t' => (string) ($hero['primary_btn_label'] ?? ''),
                ':b1u' => (string) ($hero['primary_btn_url'] ?? ''),
                ':b2t' => (string) ($hero['secondary_btn_label'] ?? ''),
                ':b2u' => (string) ($hero['secondary_btn_url'] ?? ''),
                ':oi' => 0,
                ':active' => (int) ($hero['is_active'] ?? 1),
            ]);

            return;
        }

        $pdo->exec(
            "INSERT INTO home_banners (title, subtitle, image_path, show_image, button1_text, button1_link, button2_text, button2_link, order_index, is_active)
             VALUES
             ('Plan your next journey','Clean, modern travel management with visas, flights, hotels, and insurance — all in one place.',NULL,0,'Apply Now','/#apply','Contact Us','/contact',0,1)"
        );
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS home_banners');
    },
];
