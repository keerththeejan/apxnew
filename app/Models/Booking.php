<?php

declare(strict_types=1);

namespace App\Models;

final class Booking extends Model
{
    public static function create(array $data): string
    {
        $code = strtoupper(bin2hex(random_bytes(4)));

        $stmt = self::pdo()->prepare('INSERT INTO bookings (code, type, full_name, phone, email, destination, travel_date, notes, status, created_at, updated_at) VALUES (:code, :type, :full_name, :phone, :email, :destination, :travel_date, :notes, :status, NOW(), NOW())');
        $stmt->execute([
            ':code' => $code,
            ':type' => (string) ($data['type'] ?? ''),
            ':full_name' => (string) ($data['full_name'] ?? ''),
            ':phone' => (string) ($data['phone'] ?? ''),
            ':email' => (string) ($data['email'] ?? ''),
            ':destination' => (string) ($data['destination'] ?? ''),
            ':travel_date' => (string) ($data['travel_date'] ?? ''),
            ':notes' => (string) ($data['notes'] ?? ''),
            ':status' => 'new',
        ]);

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
