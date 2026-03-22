<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\CtaSection;
use App\Models\HeroSection;

final class AppearanceController extends AdminBaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.appearance', [
            'hero' => HeroSection::findByPageKey('home'),
            'ctaMid' => CtaSection::findByKey('home_mid'),
            'ctaNews' => CtaSection::findByKey('home_news'),
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ]);
    }

    public function saveHero(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }
        HeroSection::upsertHome([
            'title' => trim((string) Request::post('title', '')),
            'subtitle' => trim((string) Request::post('subtitle', '')),
            'bg_image_path' => trim((string) Request::post('bg_image_path', '')),
            'primary_btn_label' => trim((string) Request::post('primary_btn_label', '')),
            'primary_btn_url' => trim((string) Request::post('primary_btn_url', '')),
            'secondary_btn_label' => trim((string) Request::post('secondary_btn_label', '')),
            'secondary_btn_url' => trim((string) Request::post('secondary_btn_url', '')),
            'is_active' => (int) Request::post('is_active', 1),
        ]);
        $_SESSION['flash_success'] = 'Hero section saved.';
        $this->redirect('/admin/appearance');
    }

    public function saveCta(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }
        $key = (string) Request::post('section_key', '');
        if ($key !== 'home_mid' && $key !== 'home_news') {
            $this->redirect('/admin/appearance');
            return;
        }
        CtaSection::upsert($key, [
            'title' => trim((string) Request::post('title', '')),
            'subtitle' => trim((string) Request::post('subtitle', '')),
            'primary_btn_label' => trim((string) Request::post('primary_btn_label', '')),
            'primary_btn_url' => trim((string) Request::post('primary_btn_url', '')),
            'secondary_btn_label' => trim((string) Request::post('secondary_btn_label', '')),
            'secondary_btn_url' => trim((string) Request::post('secondary_btn_url', '')),
            'is_active' => (int) Request::post('is_active', 1),
        ]);
        $_SESSION['flash_success'] = 'CTA section saved.';
        $this->redirect('/admin/appearance');
    }
}
