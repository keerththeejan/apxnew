-- =============================================================================
-- APX Travel / TMS — FULL DATABASE (schema + CMS + seed)
-- MySQL 8+ | Import once into an empty database (e.g. apxp)
-- Admin: admin@example.com / admin123
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Core tables
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(40) NULL,
  locale VARCHAR(10) NOT NULL DEFAULT 'en',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  role VARCHAR(32) NOT NULL DEFAULT 'user',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  role VARCHAR(32) NOT NULL DEFAULT 'super_admin',
  last_login_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admins_email (email),
  KEY idx_admins_active (is_active)
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

CREATE TABLE IF NOT EXISTS destinations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(140) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  country VARCHAR(140) NULL,
  description TEXT NULL,
  visa_note TEXT NULL,
  image_path VARCHAR(255) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_destinations_slug (slug),
  KEY idx_destinations_active (is_active),
  KEY idx_destinations_featured (is_featured),
  KEY idx_destinations_country (country)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  destination_id BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  summary VARCHAR(255) NULL,
  requirements TEXT NULL,
  processing_days INT NULL,
  fee_from DECIMAL(10,2) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_visas_active (is_active),
  KEY idx_visas_destination (destination_id),
  CONSTRAINT fk_visas_destination FOREIGN KEY (destination_id) REFERENCES destinations(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS flights (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(180) NOT NULL,
  summary VARCHAR(255) NULL,
  origin VARCHAR(120) NULL,
  destination VARCHAR(120) NULL,
  price_from VARCHAR(40) NULL,
  is_deal TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_flights_active (is_active),
  KEY idx_flights_deal (is_deal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hotels (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  destination_id BIGINT UNSIGNED NULL,
  name VARCHAR(180) NOT NULL,
  city VARCHAR(140) NULL,
  country VARCHAR(140) NULL,
  price_from VARCHAR(40) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_hotels_active (is_active),
  KEY idx_hotels_featured (is_featured),
  KEY idx_hotels_destination (destination_id),
  CONSTRAINT fk_hotels_destination FOREIGN KEY (destination_id) REFERENCES destinations(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS insurance_packages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(180) NOT NULL,
  summary VARCHAR(255) NULL,
  coverage_text TEXT NULL,
  price_from VARCHAR(40) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_insurance_active (is_active),
  KEY idx_insurance_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bookings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NULL,
  code VARCHAR(20) NOT NULL,
  type ENUM('visa','flight','hotel','insurance') NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(190) NULL,
  destination VARCHAR(120) NULL,
  travel_date VARCHAR(20) NULL,
  notes TEXT NULL,
  status ENUM('new','in_progress','confirmed','cancelled') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_bookings_code (code),
  KEY idx_bookings_user (user_id),
  KEY idx_bookings_type (type),
  KEY idx_bookings_status (status),
  KEY idx_bookings_created (created_at),
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inquiries (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(190) NULL,
  service VARCHAR(80) NULL,
  message TEXT NOT NULL,
  status ENUM('new','read','closed') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_inquiries_status (status),
  KEY idx_inquiries_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonials (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_name VARCHAR(120) NOT NULL,
  customer_title VARCHAR(120) NULL,
  rating TINYINT UNSIGNED NULL,
  message TEXT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_testimonials_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  author_admin_id BIGINT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL,
  excerpt VARCHAR(255) NULL,
  content MEDIUMTEXT NULL,
  cover_image_path VARCHAR(255) NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  published_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_blog_slug (slug),
  KEY idx_blog_status (status),
  KEY idx_blog_published (published_at),
  KEY idx_blog_author (author_admin_id),
  CONSTRAINT fk_blog_author FOREIGN KEY (author_admin_id) REFERENCES admins(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(120) NOT NULL,
  `value` TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_settings_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  icon VARCHAR(60) NULL,
  title VARCHAR(160) NOT NULL,
  description VARCHAR(255) NULL,
  link VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_services_active (is_active),
  KEY idx_services_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(60) NOT NULL,
  title VARCHAR(180) NOT NULL,
  slug VARCHAR(190) NULL,
  content MEDIUMTEXT NULL,
  meta_title VARCHAR(255) NULL,
  meta_description VARCHAR(512) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  updated_by_admin_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pages_key (`key`),
  UNIQUE KEY uq_pages_slug (slug),
  KEY idx_pages_active (is_active),
  CONSTRAINT fk_pages_admin FOREIGN KEY (updated_by_admin_id) REFERENCES admins(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS posts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  author_admin_id BIGINT UNSIGNED NULL,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(240) NOT NULL,
  content MEDIUMTEXT NULL,
  image_path VARCHAR(255) NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  publish_date DATE NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_posts_slug (slug),
  KEY idx_posts_status (status),
  KEY idx_posts_publish_date (publish_date),
  KEY idx_posts_author (author_admin_id),
  CONSTRAINT fk_posts_author FOREIGN KEY (author_admin_id) REFERENCES admins(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS applications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(40) NOT NULL,
  service_type VARCHAR(80) NULL,
  message TEXT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  form_data_json MEDIUMTEXT NULL,
  is_contacted TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_applications_contacted (is_contacted),
  KEY idx_applications_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(40) NULL,
  subject VARCHAR(180) NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_read (is_read),
  KEY idx_contact_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id BIGINT UNSIGNED NULL,
  provider ENUM('stripe','paypal') NOT NULL,
  provider_reference VARCHAR(190) NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  status ENUM('initiated','succeeded','failed','refunded') NOT NULL DEFAULT 'initiated',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_payments_booking (booking_id),
  KEY idx_payments_status (status),
  CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- CMS / dynamic site tables
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS nav_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  parent_id BIGINT UNSIGNED NULL,
  label VARCHAR(120) NOT NULL,
  icon VARCHAR(80) NULL,
  url VARCHAR(500) NOT NULL,
  slug VARCHAR(190) NULL,
  order_index INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  open_new_tab TINYINT(1) NOT NULL DEFAULT 0,
  is_button TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_nav_order_index (order_index),
  KEY idx_nav_active (is_active),
  KEY idx_nav_parent (parent_id),
  CONSTRAINT fk_nav_parent FOREIGN KEY (parent_id) REFERENCES nav_items(id) ON DELETE SET NULL ON UPDATE CASCADE
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

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Seed data (idempotent where possible)
-- =============================================================================

INSERT INTO admins (name, email, password_hash, is_active) VALUES
('Admin', 'admin@example.com', '$2y$10$8XcHpYb6Q6PLeqXuerADUeX5v1rby4DJfr4gIqDbtus2e0pPMvpRS', 1)
ON DUPLICATE KEY UPDATE
name = VALUES(name),
password_hash = VALUES(password_hash),
is_active = VALUES(is_active);

INSERT INTO settings (`key`, `value`) VALUES
('site_name','APX'),
('home_hero_subtitle','Clean, modern travel agency website with dynamic bookings.'),
('contact_email','info@apx.com'),
('contact_phone','+94770000000'),
('contact_phone_label','+94 77 000 0000'),
('contact_address','Colombo, Sri Lanka'),
('footer_tagline','Your joyful journey is in our care'),
('social_facebook','#'),
('social_instagram','#'),
('social_youtube','#'),
('social_tiktok','#'),
('whatsapp_number','94770000000'),
('about_subtitle','We help you travel with confidence.'),
('about_text','We provide travel services including visas, flights, hotels, and insurance.'),
('nav_apply_label','Apply Now'),
('nav_apply_url','/#apply'),
('nav_contact_label','Contact'),
('nav_contact_url','/contact'),
('site_logo_path','/images/logo.png'),
('theme_default','light')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

INSERT INTO destinations (name, slug, country, description, visa_note, is_featured, is_active, sort_order) VALUES
('Dubai','dubai','UAE','A modern city with iconic attractions and world-class shopping.','Tourist visa required for many nationalities. Contact us for the latest checklist.',1,1,1),
('Singapore','singapore','Singapore','A clean, vibrant city-state known for attractions and food.','E-visa may be required depending on nationality.',1,1,2),
('Bangkok','bangkok','Thailand','Culture, street food, and shopping.','Visa on arrival may apply for certain passports.',1,1,3)
ON DUPLICATE KEY UPDATE slug = slug;

INSERT INTO flights (title, summary, origin, destination, price_from, is_deal, is_active) VALUES
('Colombo to Dubai','Limited-time fares for selected dates.','CMB','DXB','$299',1,1),
('Colombo to Singapore','Best-value options with flexible dates.','CMB','SIN','$259',1,1)
ON DUPLICATE KEY UPDATE title = title;

INSERT INTO hotels (name, city, country, price_from, is_featured, is_active) VALUES
('Marina Bay Stay','Singapore','Singapore','$120/night',1,1),
('Downtown Comfort','Dubai','UAE','$110/night',1,1)
ON DUPLICATE KEY UPDATE name = name;

INSERT INTO insurance_packages (name, summary, coverage_text, price_from, sort_order, is_active) VALUES
('Basic Cover','Essential medical + trip assistance.','Medical emergencies, trip assistance, baggage cover.','$19',1,1),
('Premium Cover','Higher coverage with add-ons.','Higher medical coverage, cancellation, delays.','$39',2,1)
ON DUPLICATE KEY UPDATE name = name;

INSERT INTO visas (destination_id, title, summary, requirements, processing_days, fee_from, is_active)
SELECT d.id, 'Tourist Visa Assistance', 'Document guidance + submission support.', 'Passport, photos, itinerary, bank statement.', 7, 99.00, 1
FROM destinations d WHERE d.slug = 'dubai'
ON DUPLICATE KEY UPDATE title = title;

INSERT INTO testimonials (customer_name, customer_title, rating, message, is_active) VALUES
('Ayesha','Visa Service',5,'Fast response and very professional support.',1),
('Ravi','Flight Booking',5,'Got a great deal and smooth booking process.',1)
ON DUPLICATE KEY UPDATE customer_name = customer_name;

INSERT INTO blog_posts (author_admin_id, title, slug, excerpt, content, status, published_at)
SELECT a.id, 'Top 5 Travel Tips for 2026', 'top-5-travel-tips-2026', 'Practical tips to save money and travel smarter.', '1) Book early\n2) Keep documents ready\n3) Compare deals\n4) Buy insurance\n5) Check visa rules', 'published', NOW()
FROM admins a WHERE a.email = 'admin@example.com'
ON DUPLICATE KEY UPDATE slug = slug;

INSERT INTO services (icon, title, description, link, sort_order, is_active) VALUES
('✈️','Flight Ticket','Find the best fares and routes for your next trip.','/flights',1,1),
('🛂','Visa Services','End-to-end visa assistance with expert guidance.','/visas',2,1),
('💳','Finance','Flexible solutions to support your travel plans.','/flights#finance',3,1),
('🛡️','Insurance','Travel insurance for a safer journey.','/insurance',4,1),
('🏨','Hotel Booking','Comfortable stays curated for your destination.','/hotels',5,1)
ON DUPLICATE KEY UPDATE title = title;

INSERT INTO pages (`key`, title, slug, content, is_active)
VALUES
('about','About','about','We provide travel services including visas, flights, hotels, and insurance.',1),
('flights','Flight Ticket','flights','Ticketing, routing, and best fare support.',1),
('visas','Visa Services','visas','Visa assistance and application guidance.',1),
('finance','Finance','finance','Flexible travel finance solutions.',1),
('insurance','Insurance','insurance','Travel insurance for a safer journey.',1),
('hotels','Hotel','hotels','Comfortable stays curated for your destination.',1)
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO posts (author_admin_id, title, slug, content, status, publish_date)
SELECT a.id, 'Seasonal offers', 'seasonal-offers', 'Limited-time flight deals.', 'published', CURDATE()
FROM admins a WHERE a.email = 'admin@example.com'
ON DUPLICATE KEY UPDATE slug = slug;

INSERT INTO posts (author_admin_id, title, slug, content, status, publish_date)
SELECT a.id, 'Visa updates', 'visa-updates', 'Guidance and checklists.', 'published', CURDATE()
FROM admins a WHERE a.email = 'admin@example.com'
ON DUPLICATE KEY UPDATE slug = slug;

INSERT INTO posts (author_admin_id, title, slug, content, status, publish_date)
SELECT a.id, 'Hotel picks', 'hotel-picks', 'Best stays for your budget.', 'published', CURDATE()
FROM admins a WHERE a.email = 'admin@example.com'
ON DUPLICATE KEY UPDATE slug = slug;

INSERT INTO nav_items (parent_id, label, url, order_index, is_active, open_new_tab, is_button) VALUES
(NULL,'Home','/',1,1,0,0),(NULL,'About','/about',2,1,0,0),(NULL,'Contact','/contact',3,1,0,0),(NULL,'Flight Ticket','/flights',4,1,0,0),(NULL,'Visa','/visas',5,1,0,0),(NULL,'Finance','/flights#finance',6,1,0,0),(NULL,'Insurance','/insurance',7,1,0,0),(NULL,'Hotel','/hotels',8,1,0,0),(NULL,'News','/blog',9,1,0,0),
(NULL,'Apply Now','/#apply',10,1,0,1),(NULL,'Contact','/contact',11,1,0,1);

INSERT INTO hero_sections (page_key, title, subtitle, bg_image_path, primary_btn_label, primary_btn_url, secondary_btn_label, secondary_btn_url, is_active)
VALUES ('home','Plan your next journey','Clean, modern travel management with visas, flights, hotels, and insurance — all in one place.',NULL,'Apply Now','/#apply','Contact Us','/contact',1);

INSERT INTO cta_sections (section_key, title, subtitle, primary_btn_label, primary_btn_url, secondary_btn_label, secondary_btn_url, is_active) VALUES
('home_mid','Ready to start your application?','Submit your details and our team will contact you shortly.','Apply Now','/#apply','Contact Us','/contact',1),
('home_news','Travel smarter in 2026','Latest updates, offers, and travel tips from our team.','View all news','/blog','Contact support','/contact',1);

INSERT INTO footer_links (group_name, label, url, sort_order, is_active) VALUES
('Quick Links','Flight Ticket','/flights',1,1),('Quick Links','Visa','/visas',2,1),('Quick Links','Insurance','/insurance',3,1),('Quick Links','Hotel','/hotels',4,1),
('Discover','About','/about',1,1),('Discover','Contact','/contact',2,1),('Discover','Destinations','/destinations',3,1),('Discover','Blog','/blog',4,1);

INSERT INTO footer_gallery (image_path, alt_text, sort_order, is_active) VALUES
('/images/visa.jpg','Travel visa',1,1),('/images/flight.jpg','Flight booking',2,1),('/images/hotel.jpg','Hotel stay',3,1),('/images/hero.jpg','Hero journey',4,1),
('/images/visa.jpg','Gallery',5,1),('/images/hotel.jpg','Gallery',6,1);

INSERT INTO application_form_fields (field_name, label, field_type, options_json, is_required, sort_order, is_active) VALUES
('name','Full Name','text',NULL,1,1,1),('phone','Phone','tel',NULL,1,2,1),('email','Email','email',NULL,0,3,1),
('service_type','Service','select','[\"Flight Ticket\",\"Visa Services\",\"Finance\",\"Insurance\",\"Hotel Booking\"]',1,4,1),('message','Message','textarea',NULL,1,5,1);

-- Admin login: admin@example.com / admin123
