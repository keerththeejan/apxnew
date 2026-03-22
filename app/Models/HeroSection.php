<?php

declare(strict_types=1);

namespace App\Models;

final class HeroSection extends Model
{
    public static function findByPageKey(string $pageKey): ?array
    {
        return self::safe(function () use ($pageKey): ?array {
            $stmt = self::pdo()->prepare('SELECT * FROM hero_sections WHERE page_key = :k LIMIT 1');
            $stmt->execute([':k' => $pageKey]);
            $row = $stmt->fetch();
            return $row === false ? null : $row;
        }, null);
    }

    public static function upsertHome(array $data): void
    {
        $existing = self::findByPageKey('home');
        $pdo = self::pdo();
        if ($existing === null) {
            $stmt = $pdo->prepare('INSERT INTO hero_sections (page_key, title, subtitle, bg_image_path, primary_btn_label, primary_btn_url, secondary_btn_label, secondary_btn_url, is_active) VALUES (\'home\',:title,:subtitle,:bg,:p1,:pu1,:p2,:pu2,:active)');
        } else {
            $stmt = $pdo->prepare('UPDATE hero_sections SET title=:title, subtitle=:subtitle, bg_image_path=:bg, primary_btn_label=:p1, primary_btn_url=:pu1, secondary_btn_label=:p2, secondary_btn_url=:pu2, is_active=:active WHERE page_key = \'home\'');
        }
        $stmt->execute([
            ':title' => (string) ($data['title'] ?? ''),
            ':subtitle' => (string) ($data['subtitle'] ?? ''),
            ':bg' => (string) ($data['bg_image_path'] ?? ''),
            ':p1' => (string) ($data['primary_btn_label'] ?? ''),
            ':pu1' => (string) ($data['primary_btn_url'] ?? ''),
            ':p2' => (string) ($data['secondary_btn_label'] ?? ''),
            ':pu2' => (string) ($data['secondary_btn_url'] ?? ''),
            ':active' => (int) ($data['is_active'] ?? 1),
        ]);
    }
}
