<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\WhatsAppService;

final class Booking extends Model
{
    public static function create(array $data): string
    {
        $code = strtoupper(bin2hex(random_bytes(4)));
        $phone = (string) ($data['phone'] ?? '');
        $wa = WhatsAppService::formatPhone($phone);
        $cc = strtoupper(trim((string) ($data['country_code'] ?? '')));

        try {
            $stmt = self::pdo()->prepare('INSERT INTO bookings (code, type, full_name, phone, whatsapp_number, country_code, email, destination, travel_date, notes, status, created_at, updated_at) VALUES (:code, :type, :full_name, :phone, :whatsapp_number, :country_code, :email, :destination, :travel_date, :notes, :status, NOW(), NOW())');
            $stmt->execute([
                ':code' => $code,
                ':type' => (string) ($data['type'] ?? ''),
                ':full_name' => (string) ($data['full_name'] ?? ''),
                ':phone' => $phone,
                ':whatsapp_number' => $wa,
                ':country_code' => preg_match('/^[A-Z]{2}$/', $cc) ? $cc : null,
                ':email' => (string) ($data['email'] ?? ''),
                ':destination' => (string) ($data['destination'] ?? ''),
                ':travel_date' => (string) ($data['travel_date'] ?? ''),
                ':notes' => (string) ($data['notes'] ?? ''),
                ':status' => 'new',
            ]);
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            if (!str_contains($msg, '42S22') && !str_contains($msg, 'Unknown column')) {
                throw $e;
            }
            $stmt = self::pdo()->prepare('INSERT INTO bookings (code, type, full_name, phone, email, destination, travel_date, notes, status, created_at, updated_at) VALUES (:code, :type, :full_name, :phone, :email, :destination, :travel_date, :notes, :status, NOW(), NOW())');
            $stmt->execute([
                ':code' => $code,
                ':type' => (string) ($data['type'] ?? ''),
                ':full_name' => (string) ($data['full_name'] ?? ''),
                ':phone' => $phone,
                ':email' => (string) ($data['email'] ?? ''),
                ':destination' => (string) ($data['destination'] ?? ''),
                ':travel_date' => (string) ($data['travel_date'] ?? ''),
                ':notes' => (string) ($data['notes'] ?? ''),
                ':status' => 'new',
            ]);
        }

        return $code;
    }

    public static function findByCode(string $code): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM bookings WHERE code = :code LIMIT 1');
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function latest(int $limit): array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM bookings ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
