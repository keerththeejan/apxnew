<?php

declare(strict_types=1);

namespace App\Models;

final class CtaSection extends Model
{
    public static function findByKey(string $key): ?array
    {
        return self::safe(function () use ($key): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM cta_sections WHERE section_key = :k LIMIT 1');
            $stmt->execute([':k' => $key]);
            $row = $stmt->fetch();
            return $row === false ? null : $row;
        }, null);
    }

    /** @return list<array<string, mixed>> */
    public static function allOrdered(): array
    {
        $stmt = self::pdo()->query('SELECT * FROM cta_sections ORDER BY id ASC');
        return $stmt->fetchAll() ?: [];
    }

    public static function upsert(string $sectionKey, array $data): void
    {
        $stmt = self::pdo()->prepare('SELECT id FROM cta_sections WHERE section_key = :k LIMIT 1');
        $stmt->execute([':k' => $sectionKey]);
        $id = $stmt->fetchColumn();
        $pdo = self::pdo();
        if ($id === false) {
            $ins = $pdo->prepare('INSERT INTO cta_sections (section_key, title, subtitle, primary_btn_label, primary_btn_url, secondary_btn_label, secondary_btn_url, is_active) VALUES (:sk,:title,:subtitle,:p1,:pu1,:p2,:pu2,:active)');
            $ins->execute([
                ':sk' => $sectionKey,
                ':title' => (string) ($data['title'] ?? ''),
                ':subtitle' => (string) ($data['subtitle'] ?? ''),
                ':p1' => (string) ($data['primary_btn_label'] ?? ''),
                ':pu1' => (string) ($data['primary_btn_url'] ?? ''),
                ':p2' => (string) ($data['secondary_btn_label'] ?? ''),
                ':pu2' => (string) ($data['secondary_btn_url'] ?? ''),
                ':active' => (int) ($data['is_active'] ?? 1),
            ]);
            return;
        }
        $upd = $pdo->prepare('UPDATE cta_sections SET title=:title, subtitle=:subtitle, primary_btn_label=:p1, primary_btn_url=:pu1, secondary_btn_label=:p2, secondary_btn_url=:pu2, is_active=:active WHERE section_key=:sk');
        $upd->execute([
            ':title' => (string) ($data['title'] ?? ''),
            ':subtitle' => (string) ($data['subtitle'] ?? ''),
            ':p1' => (string) ($data['primary_btn_label'] ?? ''),
            ':pu1' => (string) ($data['primary_btn_url'] ?? ''),
            ':p2' => (string) ($data['secondary_btn_label'] ?? ''),
            ':pu2' => (string) ($data['secondary_btn_url'] ?? ''),
            ':active' => (int) ($data['is_active'] ?? 1),
            ':sk' => $sectionKey,
        ]);
    }
}
