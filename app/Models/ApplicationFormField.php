<?php

declare(strict_types=1);

namespace App\Models;

final class ApplicationFormField extends Model
{
    /** @return list<array<string, mixed>> */
    public static function activeOrdered(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM application_form_fields WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
            return $stmt->fetchAll() ?: [];
        }, []);
    }

    /**
     * Fields for display + validation: DB when available, otherwise built-in defaults (no admin setup required).
     *
     * @return list<array<string, mixed>>
     */
    public static function activeOrDefault(): array
    {
        $rows = self::activeOrdered();
        if ($rows !== []) {
            return $rows;
        }

        return self::defaultFieldsFallback();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function defaultFieldsFallback(): array
    {
        $opts = ['Flight Ticket', 'Visa Services', 'Finance', 'Insurance', 'Hotel Booking'];

        return [
            [
                'field_name' => 'name',
                'label' => 'Full Name',
                'field_type' => 'text',
                'options_json' => null,
                'is_required' => 1,
            ],
            [
                'field_name' => 'phone',
                'label' => 'Phone',
                'field_type' => 'tel',
                'options_json' => null,
                'is_required' => 1,
            ],
            [
                'field_name' => 'email',
                'label' => 'Email',
                'field_type' => 'email',
                'options_json' => null,
                'is_required' => 0,
            ],
            [
                'field_name' => 'service_type',
                'label' => 'Service',
                'field_type' => 'select',
                'options_json' => json_encode($opts, JSON_UNESCAPED_UNICODE),
                'is_required' => 1,
            ],
            [
                'field_name' => 'message',
                'label' => 'Message',
                'field_type' => 'textarea',
                'options_json' => null,
                'is_required' => 1,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function allOrdered(): array
    {
        return self::safe(function (): array {
            $stmt = self::pdo()->query('SELECT * FROM application_form_fields ORDER BY sort_order ASC, id ASC');
            return $stmt->fetchAll() ?: [];
        }, []);
    }

    public static function create(array $data): int
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare('INSERT INTO application_form_fields (field_name, label, field_type, options_json, is_required, sort_order, is_active) VALUES (:n,:label,:ft,:opt,:req,:sort,:active)');
        $stmt->execute([
            ':n' => (string) ($data['field_name'] ?? ''),
            ':label' => (string) ($data['label'] ?? ''),
            ':ft' => (string) ($data['field_type'] ?? 'text'),
            ':opt' => (string) ($data['options_json'] ?? ''),
            ':req' => (int) ($data['is_required'] ?? 1),
            ':sort' => (int) ($data['sort_order'] ?? 0),
            ':active' => (int) ($data['is_active'] ?? 1),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $stmt = self::pdo()->prepare('UPDATE application_form_fields SET field_name=:n, label=:label, field_type=:ft, options_json=:opt, is_required=:req, sort_order=:sort, is_active=:active WHERE id=:id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':n', (string) ($data['field_name'] ?? ''));
        $stmt->bindValue(':label', (string) ($data['label'] ?? ''));
        $stmt->bindValue(':ft', (string) ($data['field_type'] ?? 'text'));
        $stmt->bindValue(':opt', (string) ($data['options_json'] ?? ''));
        $stmt->bindValue(':req', (int) ($data['is_required'] ?? 1), \PDO::PARAM_INT);
        $stmt->bindValue(':sort', (int) ($data['sort_order'] ?? 0), \PDO::PARAM_INT);
        $stmt->bindValue(':active', (int) ($data['is_active'] ?? 1), \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function delete(int $id): int
    {
        $stmt = self::pdo()->prepare('DELETE FROM application_form_fields WHERE id = :id');
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
