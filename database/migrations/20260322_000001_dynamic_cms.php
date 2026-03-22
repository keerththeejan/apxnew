<?php

declare(strict_types=1);

function splitSqlDynamic(string $sql): array
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

        if ($ch === "'" && !$inDouble && $prev !== '\\') {
            $inSingle = !$inSingle;
        } elseif ($ch === '"' && !$inSingle && $prev !== '\\') {
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
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS nav_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  label VARCHAR(120) NOT NULL,
  url VARCHAR(500) NOT NULL,
  order_index INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  open_new_tab TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_nav_order_index (order_index),
  KEY idx_nav_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hero_sections (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  page_key VARCHAR(60) NOT NULL,
  title VARCHAR(220) NOT NULL,
  subtitle VARCHAR(500) NULL,
  bg_image_path VARCHAR(500) NULL,
  primary_btn_label VARCHAR(120) NULL,
  primary_btn_url VARCHAR(500) NULL,
  secondary_btn_label VARCHAR(120) NULL,
  secondary_btn_url VARCHAR(500) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_hero_page (page_key),
  KEY idx_hero_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cta_sections (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  section_key VARCHAR(60) NOT NULL,
  title VARCHAR(220) NOT NULL,
  subtitle VARCHAR(500) NULL,
  primary_btn_label VARCHAR(120) NULL,
  primary_btn_url VARCHAR(500) NULL,
  secondary_btn_label VARCHAR(120) NULL,
  secondary_btn_url VARCHAR(500) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cta_key (section_key),
  KEY idx_cta_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS footer_links (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  group_name VARCHAR(80) NOT NULL,
  label VARCHAR(160) NOT NULL,
  url VARCHAR(500) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_footer_group (group_name),
  KEY idx_footer_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS footer_gallery (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  image_path VARCHAR(500) NOT NULL,
  alt_text VARCHAR(220) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_fg_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS application_form_fields (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  field_name VARCHAR(80) NOT NULL,
  label VARCHAR(160) NOT NULL,
  field_type ENUM('text','tel','email','select','textarea','number','date') NOT NULL DEFAULT 'text',
  options_json TEXT NULL,
  is_required TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_aff_name (field_name),
  KEY idx_aff_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id BIGINT UNSIGNED NULL,
  message VARCHAR(500) NOT NULL,
  type VARCHAR(40) NOT NULL DEFAULT 'info',
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_an_admin (admin_id),
  KEY idx_an_read (is_read),
  CONSTRAINT fk_an_admin FOREIGN KEY (admin_id) REFERENCES admins(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        foreach (splitSqlDynamic($sql) as $stmt) {
            $pdo->exec($stmt);
        }

        $tryAlter = static function (PDO $pdo, string $sql): void {
            try {
                $pdo->exec($sql);
            } catch (Throwable $e) {
            }
        };

        $tryAlter($pdo, 'ALTER TABLE applications ADD COLUMN status ENUM(\'pending\',\'approved\',\'rejected\') NOT NULL DEFAULT \'pending\' AFTER message');
        $tryAlter($pdo, 'ALTER TABLE applications ADD COLUMN form_data_json MEDIUMTEXT NULL AFTER status');

        $tryAlter($pdo, 'ALTER TABLE pages ADD COLUMN meta_title VARCHAR(255) NULL AFTER content');
        $tryAlter($pdo, 'ALTER TABLE pages ADD COLUMN meta_description VARCHAR(512) NULL AFTER meta_title');

        $tryAlter($pdo, 'ALTER TABLE admins ADD COLUMN role VARCHAR(32) NOT NULL DEFAULT \'super_admin\' AFTER is_active');
        $tryAlter($pdo, 'ALTER TABLE users ADD COLUMN role VARCHAR(32) NOT NULL DEFAULT \'user\' AFTER is_active');

        $cntNav = (int) $pdo->query('SELECT COUNT(*) AS c FROM nav_items')->fetch()['c'];
        if ($cntNav === 0) {
            $pdo->exec("INSERT INTO nav_items (label, url, order_index, is_active, open_new_tab) VALUES
('Home','/',1,1,0),('About','/about',2,1,0),('Contact','/contact',3,1,0),('Flight Ticket','/flights',4,1,0),('Visa','/visas',5,1,0),('Finance','/flights#finance',6,1,0),('Insurance','/insurance',7,1,0),('Hotel','/hotels',8,1,0),('News','/blog',9,1,0)");
        }

        $cntHero = (int) $pdo->query('SELECT COUNT(*) AS c FROM hero_sections')->fetch()['c'];
        if ($cntHero === 0) {
            $pdo->exec("INSERT INTO hero_sections (page_key, title, subtitle, bg_image_path, primary_btn_label, primary_btn_url, secondary_btn_label, secondary_btn_url, is_active)
VALUES ('home','Plan your next journey','Clean, modern travel management with visas, flights, hotels, and insurance — all in one place.',NULL,'Apply Now','/#apply','Contact Us','/contact',1)");
        }

        $cntCta = (int) $pdo->query('SELECT COUNT(*) AS c FROM cta_sections')->fetch()['c'];
        if ($cntCta === 0) {
            $pdo->exec("INSERT INTO cta_sections (section_key, title, subtitle, primary_btn_label, primary_btn_url, secondary_btn_label, secondary_btn_url, is_active) VALUES
('home_mid','Ready to start your application?','Submit your details and our team will contact you shortly.','Apply Now','/#apply','Contact Us','/contact',1),
('home_news','Travel smarter in 2026','Latest updates, offers, and travel tips from our team.','View all news','/blog','Contact support','/contact',1)");
        }

        $cntFl = (int) $pdo->query('SELECT COUNT(*) AS c FROM footer_links')->fetch()['c'];
        if ($cntFl === 0) {
            $pdo->exec("INSERT INTO footer_links (group_name, label, url, sort_order, is_active) VALUES
('Quick Links','Flight Ticket','/flights',1,1),('Quick Links','Visa','/visas',2,1),('Quick Links','Insurance','/insurance',3,1),('Quick Links','Hotel','/hotels',4,1),
('Discover','About','/about',1,1),('Discover','Contact','/contact',2,1),('Discover','Destinations','/destinations',3,1),('Discover','Blog','/blog',4,1)");
        }

        $cntFg = (int) $pdo->query('SELECT COUNT(*) AS c FROM footer_gallery')->fetch()['c'];
        if ($cntFg === 0) {
            $pdo->exec("INSERT INTO footer_gallery (image_path, alt_text, sort_order, is_active) VALUES
('/images/visa.jpg','Travel visa',1,1),('/images/flight.jpg','Flight booking',2,1),('/images/hotel.jpg','Hotel stay',3,1),('/images/hero.jpg','Hero journey',4,1),
('/images/visa.jpg','Gallery',5,1),('/images/hotel.jpg','Gallery',6,1)");
        }

        $stmt = $pdo->query('SELECT COUNT(*) AS c FROM application_form_fields');
        $cntFf = (int) ($stmt ? $stmt->fetch()['c'] : 0);
        if ($cntFf === 0) {
            $pdo->exec("INSERT INTO application_form_fields (field_name, label, field_type, options_json, is_required, sort_order, is_active) VALUES
('name','Full Name','text',NULL,1,1,1),('phone','Phone','tel',NULL,1,2,1),('email','Email','email',NULL,0,3,1),
('service_type','Service','select','[\"Flight Ticket\",\"Visa Services\",\"Finance\",\"Insurance\",\"Hotel Booking\"]',1,4,1),('message','Message','textarea',NULL,1,5,1)");
        }

        $settingsSeed = [
            ['nav_apply_label', 'Apply Now'],
            ['nav_apply_url', '/#apply'],
            ['nav_contact_label', 'Contact'],
            ['nav_contact_url', '/contact'],
            ['site_logo_path', '/images/logo.png'],
            ['theme_default', 'light'],
        ];
        $insSet = $pdo->prepare('INSERT INTO settings (`key`, `value`) VALUES (:k, :v) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
        foreach ($settingsSeed as $row) {
            $insSet->execute([':k' => $row[0], ':v' => $row[1]]);
        }
    },
    'down' => function (PDO $pdo): void {
        $pdo->exec('DROP TABLE IF EXISTS admin_notifications');
        $pdo->exec('DROP TABLE IF EXISTS application_form_fields');
        $pdo->exec('DROP TABLE IF EXISTS footer_gallery');
        $pdo->exec('DROP TABLE IF EXISTS footer_links');
        $pdo->exec('DROP TABLE IF EXISTS cta_sections');
        $pdo->exec('DROP TABLE IF EXISTS hero_sections');
        $pdo->exec('DROP TABLE IF EXISTS nav_items');

        try {
            $pdo->exec('ALTER TABLE applications DROP COLUMN form_data_json');
        } catch (Throwable $e) {
        }
        try {
            $pdo->exec('ALTER TABLE applications DROP COLUMN status');
        } catch (Throwable $e) {
        }

        try {
            $pdo->exec('ALTER TABLE pages DROP COLUMN meta_description');
        } catch (Throwable $e) {
        }
        try {
            $pdo->exec('ALTER TABLE pages DROP COLUMN meta_title');
        } catch (Throwable $e) {
        }

        try {
            $pdo->exec('ALTER TABLE admins DROP COLUMN role');
        } catch (Throwable $e) {
        }
        try {
            $pdo->exec('ALTER TABLE users DROP COLUMN role');
        } catch (Throwable $e) {
        }
    },
];
