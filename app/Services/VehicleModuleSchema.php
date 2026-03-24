<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

final class VehicleModuleSchema
{
    public static function ensure(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS branches (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(160) NOT NULL,
                code VARCHAR(60) NOT NULL,
                address VARCHAR(255) NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_branches_code (code),
                KEY idx_branches_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS vehicles (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                branch_id BIGINT UNSIGNED NULL,
                name VARCHAR(180) NOT NULL,
                vehicle_type ENUM(\'car\',\'van\',\'suv\',\'bike\',\'luxury\') NOT NULL DEFAULT \'car\',
                model VARCHAR(120) NULL,
                registration_number VARCHAR(120) NOT NULL,
                seating_capacity INT NOT NULL DEFAULT 1,
                luggage_capacity INT NOT NULL DEFAULT 0,
                fuel_type VARCHAR(80) NULL,
                image_path VARCHAR(255) NULL,
                availability_status ENUM(\'available\',\'busy\',\'maintenance\',\'offline\') NOT NULL DEFAULT \'available\',
                pricing_json MEDIUMTEXT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_vehicles_registration (registration_number),
                KEY idx_vehicles_type (vehicle_type),
                KEY idx_vehicles_status (availability_status),
                KEY idx_vehicles_branch (branch_id),
                KEY idx_vehicles_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS drivers (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                branch_id BIGINT UNSIGNED NULL,
                vehicle_id BIGINT UNSIGNED NULL,
                name VARCHAR(180) NOT NULL,
                phone VARCHAR(40) NOT NULL,
                email VARCHAR(190) NULL,
                license_number VARCHAR(120) NOT NULL,
                profile_image_path VARCHAR(255) NULL,
                status ENUM(\'available\',\'busy\',\'offline\') NOT NULL DEFAULT \'available\',
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_drivers_license (license_number),
                KEY idx_drivers_status (status),
                KEY idx_drivers_branch (branch_id),
                KEY idx_drivers_vehicle (vehicle_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pricing_rules (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                branch_id BIGINT UNSIGNED NULL,
                vehicle_type ENUM(\'car\',\'van\',\'suv\',\'bike\',\'luxury\') NOT NULL,
                base_fare DECIMAL(12,2) NOT NULL DEFAULT 0,
                per_km DECIMAL(12,2) NOT NULL DEFAULT 0,
                per_hour DECIMAL(12,2) NOT NULL DEFAULT 0,
                per_day DECIMAL(12,2) NOT NULL DEFAULT 0,
                waiting_per_hour DECIMAL(12,2) NOT NULL DEFAULT 0,
                extra_km_charge DECIMAL(12,2) NOT NULL DEFAULT 0,
                extra_km_threshold DECIMAL(12,2) NOT NULL DEFAULT 0,
                night_charge_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
                peak_charge_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
                peak_start TIME NULL,
                peak_end TIME NULL,
                night_start TIME NULL,
                night_end TIME NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_pricing_type (vehicle_type),
                KEY idx_pricing_branch (branch_id),
                KEY idx_pricing_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS booking_coupons (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                code VARCHAR(80) NOT NULL,
                label VARCHAR(180) NULL,
                discount_type ENUM(\'percent\',\'flat\') NOT NULL DEFAULT \'percent\',
                discount_value DECIMAL(12,2) NOT NULL DEFAULT 0,
                max_discount_amount DECIMAL(12,2) NULL,
                min_booking_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                valid_from DATETIME NULL,
                valid_to DATETIME NULL,
                usage_limit INT NULL,
                used_count INT NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_booking_coupons_code (code),
                KEY idx_booking_coupons_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS vehicle_bookings (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                booking_ref VARCHAR(32) NOT NULL,
                branch_id BIGINT UNSIGNED NULL,
                vehicle_id BIGINT UNSIGNED NULL,
                driver_id BIGINT UNSIGNED NULL,
                coupon_id BIGINT UNSIGNED NULL,
                booking_mode ENUM(\'ride\',\'rental\') NOT NULL DEFAULT \'ride\',
                trip_type ENUM(\'one_way\',\'round_trip\',\'rental\') NOT NULL DEFAULT \'one_way\',
                rental_unit ENUM(\'hourly\',\'daily\') NULL,
                vehicle_type ENUM(\'car\',\'van\',\'suv\',\'bike\',\'luxury\') NOT NULL DEFAULT \'car\',
                pickup_location VARCHAR(255) NOT NULL,
                pickup_lat DECIMAL(10,7) NULL,
                pickup_lng DECIMAL(10,7) NULL,
                drop_location VARCHAR(255) NULL,
                drop_lat DECIMAL(10,7) NULL,
                drop_lng DECIMAL(10,7) NULL,
                pickup_datetime DATETIME NOT NULL,
                return_datetime DATETIME NULL,
                passenger_count INT NOT NULL DEFAULT 1,
                luggage_count INT NOT NULL DEFAULT 0,
                customer_name VARCHAR(190) NOT NULL,
                customer_phone VARCHAR(40) NOT NULL,
                customer_email VARCHAR(190) NULL,
                customer_notes TEXT NULL,
                distance_km DECIMAL(12,2) NOT NULL DEFAULT 0,
                duration_minutes INT NOT NULL DEFAULT 0,
                estimated_total DECIMAL(12,2) NOT NULL DEFAULT 0,
                currency_code VARCHAR(10) NOT NULL DEFAULT \'LKR\',
                pricing_breakdown_json MEDIUMTEXT NULL,
                status ENUM(\'pending\',\'confirmed\',\'assigned\',\'on_trip\',\'completed\',\'cancelled\') NOT NULL DEFAULT \'pending\',
                otp_code VARCHAR(12) NULL,
                otp_verified_at DATETIME NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_vehicle_bookings_ref (booking_ref),
                KEY idx_vehicle_bookings_status (status),
                KEY idx_vehicle_bookings_pickup (pickup_datetime),
                KEY idx_vehicle_bookings_type (vehicle_type),
                KEY idx_vehicle_bookings_vehicle (vehicle_id),
                KEY idx_vehicle_bookings_driver (driver_id),
                KEY idx_vehicle_bookings_branch (branch_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS booking_status_logs (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                booking_id BIGINT UNSIGNED NOT NULL,
                old_status VARCHAR(30) NULL,
                new_status VARCHAR(30) NOT NULL,
                changed_by_admin_id BIGINT UNSIGNED NULL,
                notes VARCHAR(255) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_booking_status_logs_booking (booking_id),
                KEY idx_booking_status_logs_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS vehicle_availability (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                vehicle_id BIGINT UNSIGNED NOT NULL,
                booking_id BIGINT UNSIGNED NULL,
                start_at DATETIME NOT NULL,
                end_at DATETIME NOT NULL,
                availability_status ENUM(\'reserved\',\'blocked\',\'maintenance\') NOT NULL DEFAULT \'reserved\',
                notes VARCHAR(255) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_vehicle_availability_vehicle (vehicle_id),
                KEY idx_vehicle_availability_range (start_at, end_at),
                KEY idx_vehicle_availability_booking (booking_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS booking_otp_verifications (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                booking_id BIGINT UNSIGNED NOT NULL,
                otp_code VARCHAR(12) NOT NULL,
                expires_at DATETIME NOT NULL,
                verified_at DATETIME NULL,
                attempts INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_booking_otp_booking (booking_id),
                KEY idx_booking_otp_expires (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS vehicle_maintenance_logs (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                vehicle_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(190) NOT NULL,
                details TEXT NULL,
                maintenance_date DATE NOT NULL,
                next_due_date DATE NULL,
                status ENUM(\'scheduled\',\'in_progress\',\'completed\') NOT NULL DEFAULT \'scheduled\',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_vehicle_maintenance_vehicle (vehicle_id),
                KEY idx_vehicle_maintenance_date (maintenance_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $branchCount = (int) ($pdo->query('SELECT COUNT(*) AS c FROM branches')->fetch()['c'] ?? 0);
        if ($branchCount === 0) {
            $pdo->exec("INSERT INTO branches (name, code, address, is_active) VALUES ('Main Branch', 'MAIN', 'Head Office', 1)");
        }
        $pricingCount = (int) ($pdo->query('SELECT COUNT(*) AS c FROM pricing_rules')->fetch()['c'] ?? 0);
        if ($pricingCount === 0) {
            $pdo->exec("INSERT INTO pricing_rules (branch_id, vehicle_type, base_fare, per_km, per_hour, per_day, waiting_per_hour, extra_km_charge, extra_km_threshold, night_charge_percent, peak_charge_percent, peak_start, peak_end, night_start, night_end, is_active)
                VALUES
                (1, 'car', 500, 120, 900, 4200, 350, 140, 40, 15, 12, '17:30:00', '20:30:00', '22:00:00', '05:00:00', 1),
                (1, 'van', 800, 180, 1300, 5600, 450, 220, 40, 15, 12, '17:30:00', '20:30:00', '22:00:00', '05:00:00', 1),
                (1, 'suv', 900, 220, 1500, 6400, 550, 260, 40, 15, 12, '17:30:00', '20:30:00', '22:00:00', '05:00:00', 1),
                (1, 'bike', 250, 60, 500, 2200, 150, 75, 40, 10, 8, '17:30:00', '20:30:00', '22:00:00', '05:00:00', 1),
                (1, 'luxury', 1800, 360, 2800, 12500, 900, 420, 40, 20, 18, '17:30:00', '20:30:00', '22:00:00', '05:00:00', 1)");
        }
    }
}
