-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 24, 2026 at 08:20 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apxp`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` bigint UNSIGNED DEFAULT NULL,
  `meta_json` text COLLATE utf8mb4_unicode_ci,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_al_created` (`created_at`),
  KEY `idx_al_admin` (`admin_id`),
  KEY `idx_al_entity` (`entity`,`entity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `admin_id`, `action`, `entity`, `entity_id`, `meta_json`, `ip`, `created_at`) VALUES
(1, 1, 'finance_service.create', 'finance_service', 1, NULL, '::1', '2026-03-22 08:40:46'),
(2, 1, 'settings.save', 'settings', NULL, '{\"keys\":\"site+advanced+security\"}', '::1', '2026-03-22 08:54:03'),
(3, 1, 'settings.save', 'settings', NULL, '{\"keys\":\"site+advanced+security\"}', '::1', '2026-03-22 10:09:47'),
(4, 1, 'service.delete', 'service', 35, NULL, '::1', '2026-03-22 10:17:58'),
(5, 1, 'service.delete', 'service', 22, NULL, '::1', '2026-03-22 10:18:01'),
(6, 1, 'service.delete', 'service', 27, NULL, '::1', '2026-03-22 10:18:04'),
(7, 1, 'service.delete', 'service', 32, NULL, '::1', '2026-03-22 10:18:08'),
(8, 1, 'service.delete', 'service', 3, NULL, '::1', '2026-03-22 10:18:12'),
(9, 1, 'service.delete', 'service', 30, NULL, '::1', '2026-03-22 10:18:26'),
(10, 1, 'admin.login', 'admin', 1, '{\"ip\":\"::1\"}', '::1', '2026-03-23 02:53:46'),
(11, 1, 'service.delete', 'service', 8, NULL, '::1', '2026-03-23 02:53:59'),
(12, 1, 'service.delete', 'service', 13, NULL, '::1', '2026-03-23 02:54:01'),
(13, 1, 'service.delete', 'service', 18, NULL, '::1', '2026-03-23 02:54:03'),
(14, 1, 'service.delete', 'service', 23, NULL, '::1', '2026-03-23 02:54:06'),
(15, 1, 'service.delete', 'service', 28, NULL, '::1', '2026-03-23 02:54:09'),
(16, 1, 'service.delete', 'service', 33, NULL, '::1', '2026-03-23 02:54:11'),
(17, 1, 'service.delete', 'service', 4, NULL, '::1', '2026-03-23 02:54:13'),
(18, 1, 'service.delete', 'service', 9, NULL, '::1', '2026-03-23 02:54:15'),
(19, 1, 'service.delete', 'service', 14, NULL, '::1', '2026-03-23 02:54:16'),
(20, 1, 'service.delete', 'service', 19, NULL, '::1', '2026-03-23 02:54:18'),
(21, 1, 'service.delete', 'service', 24, NULL, '::1', '2026-03-23 02:54:20'),
(22, 1, 'service.delete', 'service', 29, NULL, '::1', '2026-03-23 02:54:22'),
(23, 1, 'service.delete', 'service', 34, NULL, '::1', '2026-03-23 02:54:23'),
(24, 1, 'service.delete', 'service', 5, NULL, '::1', '2026-03-23 02:54:25'),
(25, 1, 'service.delete', 'service', 10, NULL, '::1', '2026-03-23 02:54:27'),
(26, 1, 'service.delete', 'service', 15, NULL, '::1', '2026-03-23 02:54:29'),
(27, 1, 'service.delete', 'service', 20, NULL, '::1', '2026-03-23 02:54:31'),
(28, 1, 'service.delete', 'service', 25, NULL, '::1', '2026-03-23 02:54:34'),
(29, 1, 'service.update', 'service', 1, NULL, '::1', '2026-03-23 03:35:20'),
(30, 1, 'service.update', 'service', 6, NULL, '::1', '2026-03-23 03:36:44'),
(31, 1, 'admin.login', 'admin', 1, '{\"ip\":\"::1\"}', '::1', '2026-03-24 02:57:05'),
(32, 1, 'settings.whatsapp.save', 'settings', NULL, '{\"keys\":\"whatsapp\"}', '::1', '2026-03-24 03:00:37'),
(33, 1, 'service.update', 'service', 1, NULL, '::1', '2026-03-24 03:22:57'),
(34, 1, 'service.update', 'service', 1, NULL, '::1', '2026-03-24 03:23:13'),
(35, 1, 'service.update', 'service', 1, NULL, '::1', '2026-03-24 03:25:58'),
(36, 1, 'service.update', 'service', 1, NULL, '::1', '2026-03-24 03:27:55'),
(37, 1, 'vehicle.create', 'vehicle', 1, NULL, '::1', '2026-03-24 07:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `role` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'super_admin',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_email` (`email`),
  KEY `idx_admins_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password_hash`, `is_active`, `role`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$8XcHpYb6Q6PLeqXuerADUeX5v1rby4DJfr4gIqDbtus2e0pPMvpRS', 1, 'super_admin', NULL, '2026-03-15 08:05:19', '2026-03-15 08:47:17');

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_attempts`
--

DROP TABLE IF EXISTS `admin_login_attempts`;
CREATE TABLE IF NOT EXISTS `admin_login_attempts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_time` (`email`,`attempted_at`),
  KEY `idx_ip_time` (`ip`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

DROP TABLE IF EXISTS `admin_notifications`;
CREATE TABLE IF NOT EXISTS `admin_notifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `message` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_an_admin` (`admin_id`),
  KEY `idx_an_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_password_resets`
--

DROP TABLE IF EXISTS `admin_password_resets`;
CREATE TABLE IF NOT EXISTS `admin_password_resets` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` bigint UNSIGNED NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_apr_token` (`token_hash`),
  KEY `idx_apr_admin` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
CREATE TABLE IF NOT EXISTS `applications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_type` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `form_data_json` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_contacted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_applications_contacted` (`is_contacted`),
  KEY `idx_applications_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_form_fields`
--

DROP TABLE IF EXISTS `application_form_fields`;
CREATE TABLE IF NOT EXISTS `application_form_fields` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `field_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_type` enum('text','tel','email','select','textarea','number','date') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `options_json` text COLLATE utf8mb4_unicode_ci,
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_aff_name` (`field_name`),
  KEY `idx_aff_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_form_fields`
--

INSERT INTO `application_form_fields` (`id`, `field_name`, `label`, `field_type`, `options_json`, `is_required`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'name', 'Full Name', 'text', NULL, 1, 1, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(2, 'phone', 'Phone', 'tel', NULL, 1, 2, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(3, 'email', 'Email', 'email', NULL, 0, 3, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(4, 'service_type', 'Service', 'select', '[\"Flight Ticket\",\"Visa Services\",\"Finance\",\"Insurance\",\"Hotel Booking\"]', 1, 4, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(5, 'message', 'Message', 'textarea', NULL, 1, 5, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `author_admin_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(240) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `cover_image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blog_slug` (`slug`),
  KEY `idx_blog_status` (`status`),
  KEY `idx_blog_published` (`published_at`),
  KEY `idx_blog_author` (`author_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `author_admin_id`, `title`, `slug`, `excerpt`, `content`, `cover_image_path`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Top 5 Travel Tips for 2026', 'top-5-travel-tips-2026', 'Practical tips to save money and travel smarter.', '1) Book early\n2) Keep documents ready\n3) Compare deals\n4) Buy insurance\n5) Check visa rules', NULL, 'published', '2026-03-15 08:05:19', '2026-03-15 08:05:19', '2026-03-15 08:05:19');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('visa','flight','hotel','insurance') COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `travel_date` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('new','in_progress','confirmed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bookings_code` (`code`),
  KEY `idx_bookings_user` (`user_id`),
  KEY `idx_bookings_type` (`type`),
  KEY `idx_bookings_status` (`status`),
  KEY `idx_bookings_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_coupons`
--

DROP TABLE IF EXISTS `booking_coupons`;
CREATE TABLE IF NOT EXISTS `booking_coupons` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_type` enum('percent','flat') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `discount_value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `max_discount_amount` decimal(12,2) DEFAULT NULL,
  `min_booking_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `used_count` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_booking_coupons_code` (`code`),
  KEY `idx_booking_coupons_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_otp_verifications`
--

DROP TABLE IF EXISTS `booking_otp_verifications`;
CREATE TABLE IF NOT EXISTS `booking_otp_verifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` bigint UNSIGNED NOT NULL,
  `otp_code` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `attempts` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_otp_booking` (`booking_id`),
  KEY `idx_booking_otp_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_status_logs`
--

DROP TABLE IF EXISTS `booking_status_logs`;
CREATE TABLE IF NOT EXISTS `booking_status_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` bigint UNSIGNED NOT NULL,
  `old_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by_admin_id` bigint UNSIGNED DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_status_logs_booking` (`booking_id`),
  KEY `idx_booking_status_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
CREATE TABLE IF NOT EXISTS `branches` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_branches_code` (`code`),
  KEY `idx_branches_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `code`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Main Branch', 'MAIN', 'Head Office', 1, '2026-03-24 07:52:57', '2026-03-24 07:52:57');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_read` (`is_read`),
  KEY `idx_contact_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cta_sections`
--

DROP TABLE IF EXISTS `cta_sections`;
CREATE TABLE IF NOT EXISTS `cta_sections` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_key` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_btn_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_btn_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_btn_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_btn_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cta_key` (`section_key`),
  KEY `idx_cta_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cta_sections`
--

INSERT INTO `cta_sections` (`id`, `section_key`, `title`, `subtitle`, `primary_btn_label`, `primary_btn_url`, `secondary_btn_label`, `secondary_btn_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'home_mid', 'Ready to start your application?', 'Submit your details and our team will contact you shortly.', 'Apply Now', '/#apply', 'Contact Us', '/contact', 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(2, 'home_news', 'Travel smarter in 2026', 'Latest updates, offers, and travel tips from our team.', 'View all news', '/blog', 'Contact support', '/contact', 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38');

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

DROP TABLE IF EXISTS `destinations`;
CREATE TABLE IF NOT EXISTS `destinations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `visa_note` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_destinations_slug` (`slug`),
  KEY `idx_destinations_active` (`is_active`),
  KEY `idx_destinations_featured` (`is_featured`),
  KEY `idx_destinations_country` (`country`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `name`, `slug`, `country`, `description`, `visa_note`, `image_path`, `is_featured`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Dubai', 'dubai', 'UAE', 'A modern city with iconic attractions and world-class shopping.', 'Tourist visa required for many nationalities. Contact us for the latest checklist.', NULL, 1, 1, 1, '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(2, 'Singapore', 'singapore', 'Singapore', 'A clean, vibrant city-state known for attractions and food.', 'E-visa may be required depending on nationality.', NULL, 1, 1, 2, '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(3, 'Bangkok', 'bangkok', 'Thailand', 'Culture, street food, and shopping.', 'Visa on arrival may apply for certain passports.', NULL, 1, 1, 3, '2026-03-15 08:05:19', '2026-03-15 08:05:19');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
CREATE TABLE IF NOT EXISTS `drivers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `vehicle_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_number` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('available','busy','offline') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_drivers_license` (`license_number`),
  KEY `idx_drivers_status` (`status`),
  KEY `idx_drivers_branch` (`branch_id`),
  KEY `idx_drivers_vehicle` (`vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance_services`
--

DROP TABLE IF EXISTS `finance_services`;
CREATE TABLE IF NOT EXISTS `finance_services` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','active') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fs_status` (`status`),
  KEY `idx_fs_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `finance_services`
--

INSERT INTO `finance_services` (`id`, `title`, `description`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'ff', 'f', 'draft', 0, '2026-03-22 08:40:46', '2026-03-22 08:40:46');

-- --------------------------------------------------------

--
-- Table structure for table `flights`
--

DROP TABLE IF EXISTS `flights`;
CREATE TABLE IF NOT EXISTS `flights` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origin` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_from` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_deal` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_flights_active` (`is_active`),
  KEY `idx_flights_deal` (`is_deal`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `flights`
--

INSERT INTO `flights` (`id`, `title`, `summary`, `origin`, `destination`, `price_from`, `is_deal`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Colombo to Dubai', 'Limited-time fares for selected dates.', 'CMB', 'DXB', '$299', 1, 1, '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(3, 'Colombo to Dubai', 'Limited-time fares for selected dates.', 'CMB', 'DXB', '$299', 1, 1, '2026-03-15 08:09:33', '2026-03-15 08:09:33'),
(4, 'Colombo to Singapore', 'Best-value options with flexible dates.', 'CMB', 'SIN', '$259', 1, 1, '2026-03-15 08:09:33', '2026-03-15 08:09:33'),
(5, 'Colombo to Dubai', 'Limited-time fares for selected dates.', 'CMB', 'DXB', '$299', 1, 1, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(6, 'Colombo to Singapore', 'Best-value options with flexible dates.', 'CMB', 'SIN', '$259', 1, 1, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(7, 'Colombo to Dubai', 'Limited-time fares for selected dates.', 'CMB', 'DXB', '$299', 1, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(8, 'Colombo to Singapore', 'Best-value options with flexible dates.', 'CMB', 'SIN', '$259', 1, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(9, 'Colombo to Dubai', 'Limited-time fares for selected dates.', 'CMB', 'DXB', '$299', 1, 1, '2026-03-22 08:16:53', '2026-03-22 08:16:53'),
(10, 'Colombo to Singapore', 'Best-value options with flexible dates.', 'CMB', 'SIN', '$259', 1, 1, '2026-03-22 08:16:53', '2026-03-22 08:16:53'),
(11, 'Colombo to Dubai', 'Limited-time fares for selected dates.', 'CMB', 'DXB', '$299', 1, 1, '2026-03-22 09:21:49', '2026-03-22 09:21:49'),
(12, 'Colombo to Singapore', 'Best-value options with flexible dates.', 'CMB', 'SIN', '$259', 1, 1, '2026-03-22 09:21:49', '2026-03-22 09:21:49'),
(13, 'Colombo to Dubai', 'Limited-time fares for selected dates.', 'CMB', 'DXB', '$299', 1, 1, '2026-03-22 09:27:14', '2026-03-22 09:27:14'),
(14, 'Colombo to Singapore', 'Best-value options with flexible dates.', 'CMB', 'SIN', '$259', 1, 1, '2026-03-22 09:27:14', '2026-03-22 09:27:14'),
(15, 'Colombo to Dubai', 'Limited-time fares for selected dates.', 'CMB', 'DXB', '$299', 1, 1, '2026-03-22 09:27:22', '2026-03-22 09:27:22'),
(16, 'Colombo to Singapore', 'Best-value options with flexible dates.', 'CMB', 'SIN', '$259', 1, 1, '2026-03-22 09:27:22', '2026-03-22 09:27:22'),
(17, 'Colombo to Dubai', 'Limited-time fares for selected dates.', 'CMB', 'DXB', '$299', 1, 1, '2026-03-22 09:47:58', '2026-03-22 09:47:58'),
(18, 'Colombo to Singapore', 'Best-value options with flexible dates.', 'CMB', 'SIN', '$259', 1, 1, '2026-03-22 09:47:58', '2026-03-22 09:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `footer_gallery`
--

DROP TABLE IF EXISTS `footer_gallery`;
CREATE TABLE IF NOT EXISTS `footer_gallery` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(220) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fg_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `footer_gallery`
--

INSERT INTO `footer_gallery` (`id`, `image_path`, `alt_text`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '/images/visa.jpg', 'Travel visa', 1, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(2, '/images/flight.jpg', 'Flight booking', 2, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(3, '/images/hotel.jpg', 'Hotel stay', 3, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(4, '/images/hero.jpg', 'Hero journey', 4, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(5, '/images/visa.jpg', 'Gallery', 5, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(6, '/images/hotel.jpg', 'Gallery', 6, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38');

-- --------------------------------------------------------

--
-- Table structure for table `footer_links`
--

DROP TABLE IF EXISTS `footer_links`;
CREATE TABLE IF NOT EXISTS `footer_links` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_footer_group` (`group_name`),
  KEY `idx_footer_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `footer_links`
--

INSERT INTO `footer_links` (`id`, `group_name`, `label`, `url`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Quick Links', 'Flight Ticket', '/flights', 1, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(2, 'Quick Links', 'Visa', '/visas', 2, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(3, 'Quick Links', 'Insurance', '/insurance', 3, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(4, 'Quick Links', 'Hotel', '/hotels', 4, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(5, 'Discover', 'About', '/about', 1, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(6, 'Discover', 'Contact', '/contact', 2, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(7, 'Discover', 'Destinations', '/destinations', 3, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(8, 'Discover', 'Blog', '/blog', 4, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38');

-- --------------------------------------------------------

--
-- Table structure for table `hero_sections`
--

DROP TABLE IF EXISTS `hero_sections`;
CREATE TABLE IF NOT EXISTS `hero_sections` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_key` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bg_image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_btn_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_btn_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_btn_label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_btn_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hero_page` (`page_key`),
  KEY `idx_hero_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hero_sections`
--

INSERT INTO `hero_sections` (`id`, `page_key`, `title`, `subtitle`, `bg_image_path`, `primary_btn_label`, `primary_btn_url`, `secondary_btn_label`, `secondary_btn_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'home', 'Plan your next journey', 'Clean, modern travel management with visas, flights, hotels, and insurance — all in one place.', NULL, 'Apply Now', '/#apply', 'Contact Us', '/contact', 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38');

-- --------------------------------------------------------

--
-- Table structure for table `home_banners`
--

DROP TABLE IF EXISTS `home_banners`;
CREATE TABLE IF NOT EXISTS `home_banners` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subtitle` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_image` tinyint(1) NOT NULL DEFAULT '1',
  `button1_text` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `button1_link` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `button2_text` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `button2_link` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `order_index` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_home_banners_order` (`order_index`),
  KEY `idx_home_banners_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `home_banners`
--

INSERT INTO `home_banners` (`id`, `title`, `subtitle`, `image_path`, `show_image`, `button1_text`, `button1_link`, `button2_text`, `button2_link`, `order_index`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'hello APX', NULL, NULL, 1, '', '', '', '', 1, 1, '2026-03-22 09:48:50', '2026-03-22 09:48:50');

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

DROP TABLE IF EXISTS `hotels`;
CREATE TABLE IF NOT EXISTS `hotels` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `destination_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_from` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hotels_active` (`is_active`),
  KEY `idx_hotels_featured` (`is_featured`),
  KEY `idx_hotels_destination` (`destination_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `destination_id`, `name`, `city`, `country`, `price_from`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Marina Bay Stay', 'Singapore', 'Singapore', '$120/night', 1, 1, '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(2, NULL, 'Downtown Comfort', 'Dubai', 'UAE', '$110/night', 1, 1, '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(3, NULL, 'Marina Bay Stay', 'Singapore', 'Singapore', '$120/night', 1, 1, '2026-03-15 08:09:33', '2026-03-15 08:09:33'),
(4, NULL, 'Downtown Comfort', 'Dubai', 'UAE', '$110/night', 1, 1, '2026-03-15 08:09:33', '2026-03-15 08:09:33'),
(5, NULL, 'Marina Bay Stay', 'Singapore', 'Singapore', '$120/night', 1, 1, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(6, NULL, 'Downtown Comfort', 'Dubai', 'UAE', '$110/night', 1, 1, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(7, NULL, 'Marina Bay Stay', 'Singapore', 'Singapore', '$120/night', 1, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(8, NULL, 'Downtown Comfort', 'Dubai', 'UAE', '$110/night', 1, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(9, NULL, 'Marina Bay Stay', 'Singapore', 'Singapore', '$120/night', 1, 1, '2026-03-22 08:16:53', '2026-03-22 08:16:53'),
(10, NULL, 'Downtown Comfort', 'Dubai', 'UAE', '$110/night', 1, 1, '2026-03-22 08:16:53', '2026-03-22 08:16:53'),
(11, NULL, 'Marina Bay Stay', 'Singapore', 'Singapore', '$120/night', 1, 1, '2026-03-22 09:21:49', '2026-03-22 09:21:49'),
(12, NULL, 'Downtown Comfort', 'Dubai', 'UAE', '$110/night', 1, 1, '2026-03-22 09:21:49', '2026-03-22 09:21:49'),
(13, NULL, 'Marina Bay Stay', 'Singapore', 'Singapore', '$120/night', 1, 1, '2026-03-22 09:27:14', '2026-03-22 09:27:14'),
(14, NULL, 'Downtown Comfort', 'Dubai', 'UAE', '$110/night', 1, 1, '2026-03-22 09:27:14', '2026-03-22 09:27:14'),
(15, NULL, 'Marina Bay Stay', 'Singapore', 'Singapore', '$120/night', 1, 1, '2026-03-22 09:27:22', '2026-03-22 09:27:22'),
(16, NULL, 'Downtown Comfort', 'Dubai', 'UAE', '$110/night', 1, 1, '2026-03-22 09:27:22', '2026-03-22 09:27:22'),
(17, NULL, 'Marina Bay Stay', 'Singapore', 'Singapore', '$120/night', 1, 1, '2026-03-22 09:47:58', '2026-03-22 09:47:58'),
(18, NULL, 'Downtown Comfort', 'Dubai', 'UAE', '$110/night', 1, 1, '2026-03-22 09:47:58', '2026-03-22 09:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('new','read','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inquiries_status` (`status`),
  KEY `idx_inquiries_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `insurance_packages`
--

DROP TABLE IF EXISTS `insurance_packages`;
CREATE TABLE IF NOT EXISTS `insurance_packages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coverage_text` text COLLATE utf8mb4_unicode_ci,
  `price_from` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_insurance_active` (`is_active`),
  KEY `idx_insurance_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `insurance_packages`
--

INSERT INTO `insurance_packages` (`id`, `name`, `summary`, `coverage_text`, `price_from`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Basic Cover', 'Essential medical + trip assistance.', 'Medical emergencies, trip assistance, baggage cover.', '$19', 1, 1, '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(2, 'Premium Cover', 'Higher coverage with add-ons.', 'Higher medical coverage, cancellation, delays.', '$39', 2, 1, '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(3, 'Basic Cover', 'Essential medical + trip assistance.', 'Medical emergencies, trip assistance, baggage cover.', '$19', 1, 1, '2026-03-15 08:09:33', '2026-03-15 08:09:33'),
(4, 'Premium Cover', 'Higher coverage with add-ons.', 'Higher medical coverage, cancellation, delays.', '$39', 2, 1, '2026-03-15 08:09:33', '2026-03-15 08:09:33'),
(5, 'Basic Cover', 'Essential medical + trip assistance.', 'Medical emergencies, trip assistance, baggage cover.', '$19', 1, 1, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(6, 'Premium Cover', 'Higher coverage with add-ons.', 'Higher medical coverage, cancellation, delays.', '$39', 2, 1, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(7, 'Basic Cover', 'Essential medical + trip assistance.', 'Medical emergencies, trip assistance, baggage cover.', '$19', 1, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(8, 'Premium Cover', 'Higher coverage with add-ons.', 'Higher medical coverage, cancellation, delays.', '$39', 2, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(9, 'Basic Cover', 'Essential medical + trip assistance.', 'Medical emergencies, trip assistance, baggage cover.', '$19', 1, 1, '2026-03-22 08:16:53', '2026-03-22 08:16:53'),
(10, 'Premium Cover', 'Higher coverage with add-ons.', 'Higher medical coverage, cancellation, delays.', '$39', 2, 1, '2026-03-22 08:16:53', '2026-03-22 08:16:53'),
(11, 'Basic Cover', 'Essential medical + trip assistance.', 'Medical emergencies, trip assistance, baggage cover.', '$19', 1, 1, '2026-03-22 09:21:49', '2026-03-22 09:21:49'),
(12, 'Premium Cover', 'Higher coverage with add-ons.', 'Higher medical coverage, cancellation, delays.', '$39', 2, 1, '2026-03-22 09:21:49', '2026-03-22 09:21:49'),
(13, 'Basic Cover', 'Essential medical + trip assistance.', 'Medical emergencies, trip assistance, baggage cover.', '$19', 1, 1, '2026-03-22 09:27:14', '2026-03-22 09:27:14'),
(14, 'Premium Cover', 'Higher coverage with add-ons.', 'Higher medical coverage, cancellation, delays.', '$39', 2, 1, '2026-03-22 09:27:14', '2026-03-22 09:27:14'),
(15, 'Basic Cover', 'Essential medical + trip assistance.', 'Medical emergencies, trip assistance, baggage cover.', '$19', 1, 1, '2026-03-22 09:27:22', '2026-03-22 09:27:22'),
(16, 'Premium Cover', 'Higher coverage with add-ons.', 'Higher medical coverage, cancellation, delays.', '$39', 2, 1, '2026-03-22 09:27:22', '2026-03-22 09:27:22'),
(17, 'Basic Cover', 'Essential medical + trip assistance.', 'Medical emergencies, trip assistance, baggage cover.', '$19', 1, 1, '2026-03-22 09:47:58', '2026-03-22 09:47:58'),
(18, 'Premium Cover', 'Higher coverage with add-ons.', 'Higher medical coverage, cancellation, delays.', '$39', 2, 1, '2026-03-22 09:47:58', '2026-03-22 09:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_migrations_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `name`, `batch`, `applied_at`) VALUES
(1, '20260316_000001_schema.php', 1, '2026-03-22 09:47:58'),
(2, '20260316_000002_seed.php', 1, '2026-03-22 09:47:58'),
(3, '20260322_000001_dynamic_cms.php', 1, '2026-03-22 09:47:58'),
(4, '20260322_000003_enterprise_tms.php', 1, '2026-03-22 09:47:58'),
(5, '20260322_000004_finance_services.php', 1, '2026-03-22 09:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `nav_items`
--

DROP TABLE IF EXISTS `nav_items`;
CREATE TABLE IF NOT EXISTS `nav_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `label` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `open_new_tab` tinyint(1) NOT NULL DEFAULT '0',
  `is_button` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nav_sort` (`sort_order`),
  KEY `idx_nav_active` (`is_active`),
  KEY `idx_nav_parent` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nav_items`
--

INSERT INTO `nav_items` (`id`, `parent_id`, `label`, `icon`, `url`, `slug`, `sort_order`, `is_active`, `open_new_tab`, `is_button`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Home', NULL, '/', NULL, 0, 1, 0, 0, '2026-03-22 08:16:38', '2026-03-22 09:28:19'),
(2, NULL, 'About', NULL, '/about', NULL, 2, 1, 0, 0, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(3, NULL, 'Contact', NULL, '/contact', NULL, 3, 1, 0, 0, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(4, NULL, 'Flight Ticket', NULL, '/flights', NULL, 1, 1, 0, 0, '2026-03-22 08:16:38', '2026-03-22 09:28:19'),
(5, NULL, 'Visa', NULL, '/visas', NULL, 4, 1, 0, 0, '2026-03-22 08:16:38', '2026-03-22 09:28:19'),
(6, NULL, 'Finance', NULL, '/flights#finance', NULL, 5, 1, 0, 0, '2026-03-22 08:16:38', '2026-03-22 09:28:19'),
(9, NULL, 'News', NULL, '/blog', NULL, 8, 1, 0, 0, '2026-03-22 08:16:38', '2026-03-22 09:28:19'),
(16, NULL, 'Insurance', NULL, '/insurance', NULL, 6, 1, 0, 0, '2026-03-22 08:16:47', '2026-03-22 09:28:19'),
(17, NULL, 'Hotel', NULL, '/hotels', NULL, 7, 1, 0, 0, '2026-03-22 08:16:47', '2026-03-22 09:28:19');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE IF NOT EXISTS `pages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_by_admin_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pages_key` (`key`),
  UNIQUE KEY `uq_pages_slug` (`slug`),
  KEY `idx_pages_active` (`is_active`),
  KEY `fk_pages_admin` (`updated_by_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `key`, `title`, `slug`, `content`, `meta_title`, `meta_description`, `is_active`, `updated_by_admin_id`, `created_at`, `updated_at`) VALUES
(2, 'flights', 'Flight Ticket', 'flights', 'Ticketing, routing, and best fare support.', NULL, NULL, 1, NULL, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(3, 'visas', 'Visa Services', 'visas', 'Visa assistance and application guidance.', NULL, NULL, 1, NULL, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(4, 'finance', 'Finance', 'finance', 'Flexible travel finance solutions.', NULL, NULL, 1, NULL, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(7, 'about', 'About', 'about', 'We provide travel services including visas, flights, hotels, and insurance.', NULL, NULL, 1, NULL, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(8, 'hotels', 'Hotel', 'hotels', 'Comfortable stays curated for your destination.', NULL, NULL, 1, NULL, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(19, 'insurance', 'Insurance', 'insurance', 'Travel insurance for a safer journey.', NULL, NULL, 1, NULL, '2026-03-22 09:21:49', '2026-03-22 09:21:49');

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

DROP TABLE IF EXISTS `payment_transactions`;
CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` bigint UNSIGNED DEFAULT NULL,
  `provider` enum('stripe','paypal') COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_reference` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `status` enum('initiated','succeeded','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'initiated',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payments_booking` (`booking_id`),
  KEY `idx_payments_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE IF NOT EXISTS `posts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `author_admin_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(240) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `publish_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_posts_slug` (`slug`),
  KEY `idx_posts_status` (`status`),
  KEY `idx_posts_publish_date` (`publish_date`),
  KEY `idx_posts_author` (`author_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `author_admin_id`, `title`, `slug`, `content`, `image_path`, `status`, `publish_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'Seasonal offers', 'seasonal-offers', 'Limited-time flight deals.', NULL, 'published', '2026-03-15', '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(2, 1, 'Visa updates', 'visa-updates', 'Guidance and checklists.', NULL, 'published', '2026-03-15', '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(3, 1, 'Hotel picks', 'hotel-picks', 'Best stays for your budget.', NULL, 'published', '2026-03-15', '2026-03-15 08:41:45', '2026-03-15 08:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `pricing_rules`
--

DROP TABLE IF EXISTS `pricing_rules`;
CREATE TABLE IF NOT EXISTS `pricing_rules` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `vehicle_type` enum('car','van','suv','bike','luxury') COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_fare` decimal(12,2) NOT NULL DEFAULT '0.00',
  `per_km` decimal(12,2) NOT NULL DEFAULT '0.00',
  `per_hour` decimal(12,2) NOT NULL DEFAULT '0.00',
  `per_day` decimal(12,2) NOT NULL DEFAULT '0.00',
  `waiting_per_hour` decimal(12,2) NOT NULL DEFAULT '0.00',
  `extra_km_charge` decimal(12,2) NOT NULL DEFAULT '0.00',
  `extra_km_threshold` decimal(12,2) NOT NULL DEFAULT '0.00',
  `night_charge_percent` decimal(6,2) NOT NULL DEFAULT '0.00',
  `peak_charge_percent` decimal(6,2) NOT NULL DEFAULT '0.00',
  `peak_start` time DEFAULT NULL,
  `peak_end` time DEFAULT NULL,
  `night_start` time DEFAULT NULL,
  `night_end` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pricing_type` (`vehicle_type`),
  KEY `idx_pricing_branch` (`branch_id`),
  KEY `idx_pricing_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pricing_rules`
--

INSERT INTO `pricing_rules` (`id`, `branch_id`, `vehicle_type`, `base_fare`, `per_km`, `per_hour`, `per_day`, `waiting_per_hour`, `extra_km_charge`, `extra_km_threshold`, `night_charge_percent`, `peak_charge_percent`, `peak_start`, `peak_end`, `night_start`, `night_end`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'car', 500.00, 120.00, 900.00, 4200.00, 350.00, 140.00, 40.00, 15.00, 12.00, '17:30:00', '20:30:00', '22:00:00', '05:00:00', 1, '2026-03-24 07:52:57', '2026-03-24 07:52:57'),
(2, 1, 'van', 800.00, 180.00, 1300.00, 5600.00, 450.00, 220.00, 40.00, 15.00, 12.00, '17:30:00', '20:30:00', '22:00:00', '05:00:00', 1, '2026-03-24 07:52:57', '2026-03-24 07:52:57'),
(3, 1, 'suv', 900.00, 220.00, 1500.00, 6400.00, 550.00, 260.00, 40.00, 15.00, 12.00, '17:30:00', '20:30:00', '22:00:00', '05:00:00', 1, '2026-03-24 07:52:57', '2026-03-24 07:52:57'),
(4, 1, 'bike', 250.00, 60.00, 500.00, 2200.00, 150.00, 75.00, 40.00, 10.00, 8.00, '17:30:00', '20:30:00', '22:00:00', '05:00:00', 1, '2026-03-24 07:52:57', '2026-03-24 07:52:57'),
(5, 1, 'luxury', 1800.00, 360.00, 2800.00, 12500.00, 900.00, 420.00, 40.00, 20.00, 18.00, '17:30:00', '20:30:00', '22:00:00', '05:00:00', 1, '2026-03-24 07:52:57', '2026-03-24 07:52:57');

-- --------------------------------------------------------

--
-- Table structure for table `quote_routes`
--

DROP TABLE IF EXISTS `quote_routes`;
CREATE TABLE IF NOT EXISTS `quote_routes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_per_kg` decimal(12,2) NOT NULL DEFAULT '0.00',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_quote_routes_slug` (`slug`),
  KEY `idx_quote_routes_sort` (`sort_order`),
  KEY `idx_quote_routes_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quote_routes`
--

INSERT INTO `quote_routes` (`id`, `slug`, `label`, `country`, `service`, `price_per_kg`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'usa-dhl', 'USA – DHL', 'USA', 'DHL', 500.00, 1, 1, '2026-03-24 05:43:30', '2026-03-24 05:43:30'),
(2, 'uk-fedex', 'United Kingdom – FedEx', 'United Kingdom', 'FedEx', 420.00, 2, 1, '2026-03-24 05:43:30', '2026-03-24 05:43:30'),
(3, 'ae-aramex', 'UAE – Aramex', 'UAE', 'Aramex', 380.00, 3, 1, '2026-03-24 05:43:30', '2026-03-24 05:43:30'),
(4, 'lk-local', 'Sri Lanka – Local', 'Sri Lanka', 'Local', 120.00, 4, 1, '2026-03-24 05:43:30', '2026-03-24 05:43:30');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `icon` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `country_code` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_services_active` (`is_active`),
  KEY `idx_services_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `icon`, `image_path`, `title`, `description`, `link`, `sort_order`, `is_active`, `created_at`, `updated_at`, `country_code`) VALUES
(1, '✈️', '', 'Flight Ticket', 'Find the best fares and routes for your next trip.', '/flights', 1, 1, '2026-03-15 08:41:45', '2026-03-24 03:27:55', 'AU'),
(2, '🛂', NULL, 'Visa Services', 'End-to-end visa assistance with expert guidance.', '/visas', 2, 1, '2026-03-15 08:41:45', '2026-03-15 08:41:45', NULL),
(6, '✈️', '', 'Flight Ticket', 'Find the best fares and routes for your next trip.', '/flights', 1, 1, '2026-03-22 08:16:38', '2026-03-23 03:36:44', 'AF'),
(7, '🛂', NULL, 'Visa Services', 'End-to-end visa assistance with expert guidance.', '/visas', 2, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38', NULL),
(11, '✈️', NULL, 'Flight Ticket', 'Find the best fares and routes for your next trip.', '/flights', 1, 1, '2026-03-22 08:16:53', '2026-03-22 08:16:53', NULL),
(12, '🛂', NULL, 'Visa Services', 'End-to-end visa assistance with expert guidance.', '/visas', 2, 1, '2026-03-22 08:16:53', '2026-03-22 08:16:53', NULL),
(16, '✈️', NULL, 'Flight Ticket', 'Find the best fares and routes for your next trip.', '/flights', 1, 1, '2026-03-22 09:21:49', '2026-03-22 09:21:49', NULL),
(17, '🛂', NULL, 'Visa Services', 'End-to-end visa assistance with expert guidance.', '/visas', 2, 1, '2026-03-22 09:21:49', '2026-03-22 09:21:49', NULL),
(21, '✈️', NULL, 'Flight Ticket', 'Find the best fares and routes for your next trip.', '/flights', 1, 1, '2026-03-22 09:27:14', '2026-03-22 09:27:14', NULL),
(26, '✈️', NULL, 'Flight Ticket', 'Find the best fares and routes for your next trip.', '/flights', 1, 1, '2026-03-22 09:27:22', '2026-03-22 09:27:22', NULL),
(31, '✈️', NULL, 'Flight Ticket', 'Find the best fares and routes for your next trip.', '/flights', 1, 1, '2026-03-22 09:47:58', '2026-03-22 09:47:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=206 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'APX', '2026-03-15 08:05:19', '2026-03-22 08:54:03'),
(2, 'home_hero_subtitle', 'Clean, modern travel agency website with dynamic bookings.', '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(3, 'contact_email', 'info@apx.com', '2026-03-15 08:05:19', '2026-03-22 09:21:49'),
(4, 'contact_phone', '+94770000000', '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(5, 'contact_phone_label', '+94 77 000 0000', '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(6, 'contact_address', 'Colombo, Sri Lanka', '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(7, 'footer_tagline', 'Your joyful journey is in our care', '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(8, 'social_facebook', '#', '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(9, 'social_instagram', '#', '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(10, 'social_youtube', '#', '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(11, 'social_tiktok', '#', '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(12, 'whatsapp_number', '+9494770000000', '2026-03-15 08:05:19', '2026-03-24 03:00:37'),
(13, 'about_subtitle', 'We help you travel with confidence.', '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(14, 'about_text', 'We provide travel services including visas, flights, hotels, and insurance.', '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(30, 'nav_apply_label', 'Apply Now', '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(31, 'nav_apply_url', '/#apply', '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(32, 'nav_contact_label', 'Contact', '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(33, 'nav_contact_url', '/contact', '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(34, 'site_logo_path', '/images/logo.png', '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(35, 'theme_default', 'light', '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(71, 'theme_primary', '#4f8cff', '2026-03-22 08:54:03', '2026-03-22 08:54:03'),
(72, 'theme_accent', '#ff7a18', '2026-03-22 08:54:03', '2026-03-22 08:54:03'),
(73, 'theme_gradient_from', '#0f172a', '2026-03-22 08:54:03', '2026-03-22 08:54:03'),
(74, 'theme_gradient_to', '#1e293b', '2026-03-22 08:54:03', '2026-03-22 08:54:03'),
(75, 'default_theme', 'light', '2026-03-22 08:54:03', '2026-03-22 08:54:03'),
(76, 'default_locale', 'en', '2026-03-22 08:54:03', '2026-03-22 08:54:03'),
(77, 'app_timezone', 'UTC', '2026-03-22 08:54:03', '2026-03-22 08:54:03'),
(78, 'currency_format', 'USD %s', '2026-03-22 08:54:03', '2026-03-22 08:54:03'),
(79, 'login_max_attempts', '5', '2026-03-22 08:54:03', '2026-03-22 08:54:03'),
(80, 'login_lockout_minutes', '15', '2026-03-22 08:54:03', '2026-03-22 08:54:03'),
(85, 'social_links_json', '[{\"label\":\"Facebook\",\"url\":\"#\"},{\"label\":\"Instagram\",\"url\":\"#\"},{\"label\":\"YouTube\",\"url\":\"#\"},{\"label\":\"TikTok\",\"url\":\"#\"}]', '2026-03-22 08:54:03', '2026-03-22 10:09:47'),
(171, 'theme_enabled', '1', '2026-03-22 10:09:47', '2026-03-22 10:09:47'),
(172, 'theme_switcher_enabled', '1', '2026-03-22 10:09:47', '2026-03-22 10:09:47'),
(173, 'theme_mode', 'auto', '2026-03-22 10:09:47', '2026-03-22 10:09:47'),
(174, 'clock_enabled', '0', '2026-03-22 10:09:47', '2026-03-22 10:09:47'),
(175, 'clock_time_format', '24', '2026-03-22 10:09:47', '2026-03-22 10:09:47'),
(198, 'whatsapp_enabled', '0', '2026-03-24 03:00:37', '2026-03-24 03:00:37'),
(199, 'whatsapp_country_code', '94', '2026-03-24 03:00:37', '2026-03-24 03:00:37'),
(201, 'whatsapp_phone_number_id', '', '2026-03-24 03:00:37', '2026-03-24 03:00:37'),
(202, 'whatsapp_api_token', '', '2026-03-24 03:00:37', '2026-03-24 03:00:37'),
(203, 'whatsapp_tpl_new_order', 'Hello {{name}}, your booking {{code}} has been received.', '2026-03-24 03:00:37', '2026-03-24 03:00:37'),
(204, 'whatsapp_tpl_status_update', 'Hello {{name}}, your status is now {{status}}.', '2026-03-24 03:00:37', '2026-03-24 03:00:37'),
(205, 'whatsapp_tpl_service_info', 'Hello {{name}}, here is the service info: {{service}}', '2026-03-24 03:00:37', '2026-03-24 03:00:37');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_title` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` tinyint UNSIGNED DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_testimonials_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `customer_name`, `customer_title`, `rating`, `message`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Ayesha', 'Visa Service', 5, 'Fast response and very professional support.', 1, '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(2, 'Ravi', 'Flight Booking', 5, 'Got a great deal and smooth booking process.', 1, '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(3, 'Ayesha', 'Visa Service', 5, 'Fast response and very professional support.', 1, '2026-03-15 08:09:33', '2026-03-15 08:09:33'),
(4, 'Ravi', 'Flight Booking', 5, 'Got a great deal and smooth booking process.', 1, '2026-03-15 08:09:33', '2026-03-15 08:09:33'),
(5, 'Ayesha', 'Visa Service', 5, 'Fast response and very professional support.', 1, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(6, 'Ravi', 'Flight Booking', 5, 'Got a great deal and smooth booking process.', 1, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(7, 'Ayesha', 'Visa Service', 5, 'Fast response and very professional support.', 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(8, 'Ravi', 'Flight Booking', 5, 'Got a great deal and smooth booking process.', 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(9, 'Ayesha', 'Visa Service', 5, 'Fast response and very professional support.', 1, '2026-03-22 08:16:53', '2026-03-22 08:16:53'),
(10, 'Ravi', 'Flight Booking', 5, 'Got a great deal and smooth booking process.', 1, '2026-03-22 08:16:53', '2026-03-22 08:16:53'),
(11, 'Ayesha', 'Visa Service', 5, 'Fast response and very professional support.', 1, '2026-03-22 09:21:49', '2026-03-22 09:21:49'),
(12, 'Ravi', 'Flight Booking', 5, 'Got a great deal and smooth booking process.', 1, '2026-03-22 09:21:49', '2026-03-22 09:21:49'),
(13, 'Ayesha', 'Visa Service', 5, 'Fast response and very professional support.', 1, '2026-03-22 09:27:14', '2026-03-22 09:27:14'),
(14, 'Ravi', 'Flight Booking', 5, 'Got a great deal and smooth booking process.', 1, '2026-03-22 09:27:14', '2026-03-22 09:27:14'),
(15, 'Ayesha', 'Visa Service', 5, 'Fast response and very professional support.', 1, '2026-03-22 09:27:22', '2026-03-22 09:27:22'),
(16, 'Ravi', 'Flight Booking', 5, 'Got a great deal and smooth booking process.', 1, '2026-03-22 09:27:22', '2026-03-22 09:27:22'),
(17, 'Ayesha', 'Visa Service', 5, 'Fast response and very professional support.', 1, '2026-03-22 09:47:58', '2026-03-22 09:47:58'),
(18, 'Ravi', 'Flight Booking', 5, 'Got a great deal and smooth booking process.', 1, '2026-03-22 09:47:58', '2026-03-22 09:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `role` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vehicle_type` enum('car','van','suv','bike','luxury') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'car',
  `model` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_number` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seating_capacity` int NOT NULL DEFAULT '1',
  `luggage_capacity` int NOT NULL DEFAULT '0',
  `fuel_type` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `availability_status` enum('available','busy','maintenance','offline') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `pricing_json` mediumtext COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vehicles_registration` (`registration_number`),
  KEY `idx_vehicles_type` (`vehicle_type`),
  KEY `idx_vehicles_status` (`availability_status`),
  KEY `idx_vehicles_branch` (`branch_id`),
  KEY `idx_vehicles_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `branch_id`, `name`, `vehicle_type`, `model`, `registration_number`, `seating_capacity`, `luggage_capacity`, `fuel_type`, `image_path`, `availability_status`, `pricing_json`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'f', 'car', 'f', 'F', 4, 2, 'f', 'f', 'available', '{}', 1, '2026-03-24 07:53:15', '2026-03-24 07:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_availability`
--

DROP TABLE IF EXISTS `vehicle_availability`;
CREATE TABLE IF NOT EXISTS `vehicle_availability` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint UNSIGNED NOT NULL,
  `booking_id` bigint UNSIGNED DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `availability_status` enum('reserved','blocked','maintenance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reserved',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vehicle_availability_vehicle` (`vehicle_id`),
  KEY `idx_vehicle_availability_range` (`start_at`,`end_at`),
  KEY `idx_vehicle_availability_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_bookings`
--

DROP TABLE IF EXISTS `vehicle_bookings`;
CREATE TABLE IF NOT EXISTS `vehicle_bookings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_ref` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `vehicle_id` bigint UNSIGNED DEFAULT NULL,
  `driver_id` bigint UNSIGNED DEFAULT NULL,
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `booking_mode` enum('ride','rental') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ride',
  `trip_type` enum('one_way','round_trip','rental') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'one_way',
  `rental_unit` enum('hourly','daily') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_type` enum('car','van','suv','bike','luxury') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'car',
  `pickup_location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pickup_lat` decimal(10,7) DEFAULT NULL,
  `pickup_lng` decimal(10,7) DEFAULT NULL,
  `drop_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `drop_lat` decimal(10,7) DEFAULT NULL,
  `drop_lng` decimal(10,7) DEFAULT NULL,
  `pickup_datetime` datetime NOT NULL,
  `return_datetime` datetime DEFAULT NULL,
  `passenger_count` int NOT NULL DEFAULT '1',
  `luggage_count` int NOT NULL DEFAULT '0',
  `customer_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_notes` text COLLATE utf8mb4_unicode_ci,
  `distance_km` decimal(12,2) NOT NULL DEFAULT '0.00',
  `duration_minutes` int NOT NULL DEFAULT '0',
  `estimated_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LKR',
  `pricing_breakdown_json` mediumtext COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','confirmed','assigned','on_trip','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `otp_code` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vehicle_bookings_ref` (`booking_ref`),
  KEY `idx_vehicle_bookings_status` (`status`),
  KEY `idx_vehicle_bookings_pickup` (`pickup_datetime`),
  KEY `idx_vehicle_bookings_type` (`vehicle_type`),
  KEY `idx_vehicle_bookings_vehicle` (`vehicle_id`),
  KEY `idx_vehicle_bookings_driver` (`driver_id`),
  KEY `idx_vehicle_bookings_branch` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_maintenance_logs`
--

DROP TABLE IF EXISTS `vehicle_maintenance_logs`;
CREATE TABLE IF NOT EXISTS `vehicle_maintenance_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint UNSIGNED NOT NULL,
  `title` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `maintenance_date` date NOT NULL,
  `next_due_date` date DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vehicle_maintenance_vehicle` (`vehicle_id`),
  KEY `idx_vehicle_maintenance_date` (`maintenance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visas`
--

DROP TABLE IF EXISTS `visas`;
CREATE TABLE IF NOT EXISTS `visas` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `destination_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requirements` text COLLATE utf8mb4_unicode_ci,
  `processing_days` int DEFAULT NULL,
  `fee_from` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visas_active` (`is_active`),
  KEY `idx_visas_destination` (`destination_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visas`
--

INSERT INTO `visas` (`id`, `destination_id`, `title`, `summary`, `requirements`, `processing_days`, `fee_from`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Tourist Visa Assistance', 'Document guidance + submission support.', 'Passport, photos, itinerary, bank statement.', 7, 99.00, 1, '2026-03-15 08:05:19', '2026-03-15 08:05:19'),
(2, 1, 'Tourist Visa Assistance', 'Document guidance + submission support.', 'Passport, photos, itinerary, bank statement.', 7, 99.00, 1, '2026-03-15 08:09:33', '2026-03-15 08:09:33'),
(3, 1, 'Tourist Visa Assistance', 'Document guidance + submission support.', 'Passport, photos, itinerary, bank statement.', 7, 99.00, 1, '2026-03-15 08:41:45', '2026-03-15 08:41:45'),
(4, 1, 'Tourist Visa Assistance', 'Document guidance + submission support.', 'Passport, photos, itinerary, bank statement.', 7, 99.00, 1, '2026-03-22 08:16:38', '2026-03-22 08:16:38'),
(5, 1, 'Tourist Visa Assistance', 'Document guidance + submission support.', 'Passport, photos, itinerary, bank statement.', 7, 99.00, 1, '2026-03-22 08:16:53', '2026-03-22 08:16:53'),
(6, 1, 'Tourist Visa Assistance', 'Document guidance + submission support.', 'Passport, photos, itinerary, bank statement.', 7, 99.00, 1, '2026-03-22 09:21:49', '2026-03-22 09:21:49'),
(7, 1, 'Tourist Visa Assistance', 'Document guidance + submission support.', 'Passport, photos, itinerary, bank statement.', 7, 99.00, 1, '2026-03-22 09:27:14', '2026-03-22 09:27:14'),
(8, 1, 'Tourist Visa Assistance', 'Document guidance + submission support.', 'Passport, photos, itinerary, bank statement.', 7, 99.00, 1, '2026-03-22 09:27:22', '2026-03-22 09:27:22'),
(9, 1, 'Tourist Visa Assistance', 'Document guidance + submission support.', 'Passport, photos, itinerary, bank statement.', 7, 99.00, 1, '2026-03-22 09:47:58', '2026-03-22 09:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_logs`
--

DROP TABLE IF EXISTS `whatsapp_logs`;
CREATE TABLE IF NOT EXISTS `whatsapp_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `context_key` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` bigint DEFAULT NULL,
  `http_code` int NOT NULL DEFAULT '0',
  `response_body` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_whatsapp_logs_status` (`status`),
  KEY `idx_whatsapp_logs_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_logs`
--

INSERT INTO `whatsapp_logs` (`id`, `status`, `provider`, `to_phone`, `message_body`, `context_key`, `entity_id`, `http_code`, `response_body`, `created_at`) VALUES
(1, 'skipped', 'disabled', '+9494770000000', 'Service: Flight Ticket\r\nDescription: Find the best fares and routes for your next trip.\r\nLink: /flights', 'service.manual', 1, 0, 'https://wa.me/9494770000000?text=Service%3A%20Flight%20Ticket%0D%0ADescription%3A%20Find%20the%20best%20fares%20and%20routes%20for%20your%20next%20trip.%0D%0ALink%3A%20%2Fflights', '2026-03-24 03:04:35'),
(2, 'skipped', 'disabled', '+9494770000000', 'Service: Flight Ticket\r\nDescription: Find the best fares and routes for your next trip.\r\nLink: /flights', 'service.manual', 1, 0, 'https://wa.me/9494770000000?text=Service%3A%20Flight%20Ticket%0D%0ADescription%3A%20Find%20the%20best%20fares%20and%20routes%20for%20your%20next%20trip.%0D%0ALink%3A%20%2Fflights', '2026-03-24 03:04:38'),
(3, 'skipped', 'disabled', '+9494770000000', 'Hello Admin, here is the service info: Flight Ticket', 'service.updated', 1, 0, 'https://wa.me/9494770000000?text=Hello%20Admin%2C%20here%20is%20the%20service%20info%3A%20Flight%20Ticket', '2026-03-24 03:22:57'),
(4, 'skipped', 'disabled', '+9494770000000', 'Hello Admin, here is the service info: Flight Ticket', 'service.updated', 1, 0, 'https://wa.me/9494770000000?text=Hello%20Admin%2C%20here%20is%20the%20service%20info%3A%20Flight%20Ticket', '2026-03-24 03:23:13'),
(5, 'skipped', 'disabled', '+9494770000000', 'Hello Admin, here is the service info: Flight Ticket', 'service.updated', 1, 0, 'https://wa.me/9494770000000?text=Hello%20Admin%2C%20here%20is%20the%20service%20info%3A%20Flight%20Ticket', '2026-03-24 03:25:58'),
(6, 'skipped', 'disabled', '+9494770000000', 'Hello Admin, here is the service info: Flight Ticket', 'service.updated', 1, 0, 'https://wa.me/9494770000000?text=Hello%20Admin%2C%20here%20is%20the%20service%20info%3A%20Flight%20Ticket', '2026-03-24 03:27:55');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_al_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `fk_an_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `admin_password_resets`
--
ALTER TABLE `admin_password_resets`
  ADD CONSTRAINT `fk_apr_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `hotels`
--
ALTER TABLE `hotels`
  ADD CONSTRAINT `fk_hotels_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `nav_items`
--
ALTER TABLE `nav_items`
  ADD CONSTRAINT `fk_nav_parent` FOREIGN KEY (`parent_id`) REFERENCES `nav_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `fk_pages_admin` FOREIGN KEY (`updated_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_author` FOREIGN KEY (`author_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `visas`
--
ALTER TABLE `visas`
  ADD CONSTRAINT `fk_visas_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
