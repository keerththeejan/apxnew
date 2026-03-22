<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\AdminNotification;
use App\Models\Setting;

final class SettingsController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/settings');
    }

    public function index(): void
    {
        $this->requireSuperAdmin();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $s = Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');

        view('admin.settings', [
            'title' => 'APX Admin - Settings',
            'pageKey' => 'settings',
            'pageTitle' => 'Settings',
            'crumb' => $siteName . ' / Settings',
            'settings' => $s,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ]);
    }

    public function save(): void
    {
        $this->requireSuperAdmin();

        if (!Csrf::verify((string) Request::post('_token', ''))) {
            $this->respondSave(false, 'CSRF token mismatch', 419);
            return;
        }

        $uploadedLogoPath = $this->handleLogoUpload();
        if ($uploadedLogoPath === false) {
            $this->respondSave(false, 'Logo upload failed (invalid type or size).');
            return;
        }

        $tzRaw = trim((string) Request::post('app_timezone', ''));
        if ($tzRaw !== '') {
            try {
                new \DateTimeZone($tzRaw);
            } catch (\Throwable) {
                $this->respondSave(false, 'Invalid timezone. Use a valid IANA name (e.g. Asia/Colombo, UTC).');
                return;
            }
        }

        $themeMode = strtolower(trim((string) Request::post('theme_mode', 'light')));
        if (!in_array($themeMode, ['light', 'dark', 'auto'], true)) {
            $themeMode = 'light';
        }

        $clockFmt = strtolower(trim((string) Request::post('clock_time_format', '24')));
        if ($clockFmt !== '12') {
            $clockFmt = '24';
        }

        Setting::set('theme_enabled', Request::post('theme_enabled') === '1' ? '1' : '0');
        Setting::set('theme_switcher_enabled', Request::post('theme_switcher_enabled') === '1' ? '1' : '0');
        Setting::set('theme_mode', $themeMode);
        Setting::set('clock_enabled', Request::post('clock_enabled') === '1' ? '1' : '0');
        Setting::set('clock_time_format', $clockFmt);

        // Keep legacy default_theme in sync for any code still reading it.
        if ($themeMode === 'dark') {
            Setting::set('default_theme', 'dark');
        } else {
            Setting::set('default_theme', 'light');
        }

        $stringKeys = [
            'site_name',
            'site_logo_path',
            'contact_email',
            'contact_phone',
            'contact_phone_label',
            'contact_address',
            'footer_tagline',
            'theme_primary',
            'theme_accent',
            'theme_gradient_from',
            'theme_gradient_to',
            'default_locale',
            'app_timezone',
            'currency_format',
            'login_max_attempts',
            'login_lockout_minutes',
            'nav_apply_label',
            'nav_apply_url',
            'nav_contact_label',
            'nav_contact_url',
        ];

        foreach ($stringKeys as $k) {
            if ($k === 'site_logo_path' && $uploadedLogoPath !== null) {
                Setting::set($k, $uploadedLogoPath);
                continue;
            }
            Setting::set($k, trim((string) Request::post($k, '')));
        }

        $socialJson = $this->buildSocialJsonFromPost();
        Setting::set('social_links_json', $socialJson);

        try {
            ActivityLog::record(
                isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null,
                'settings.save',
                'settings',
                null,
                ['keys' => 'site+advanced+security']
            );
            AdminNotification::create('Site settings were updated.', 'info');
        } catch (\Throwable $e) {
        }

        $this->respondSave(true, 'Settings saved successfully.');
    }

    /**
     * @return null|string|false null = keep existing path; string = new path; false = error
     */
    private function handleLogoUpload()
    {
        if (!isset($_FILES['logo_file']) || !is_array($_FILES['logo_file'])) {
            return null;
        }
        $f = $_FILES['logo_file'];
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($f['error'] ?? 0) !== UPLOAD_ERR_OK || !isset($f['tmp_name']) || !is_uploaded_file($f['tmp_name'])) {
            return false;
        }
        if (($f['size'] ?? 0) > 2_500_000) {
            return false;
        }

        $mime = 'application/octet-stream';
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            if ($fi !== false) {
                $mime = (string) finfo_file($fi, $f['tmp_name']);
                finfo_close($fi);
            }
        }

        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($map[$mime])) {
            return false;
        }

        $dir = dirname(__DIR__, 3) . '/public/uploads/branding';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $name = 'logo-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
        $dest = $dir . '/' . $name;
        if (!@move_uploaded_file($f['tmp_name'], $dest)) {
            return false;
        }

        return '/uploads/branding/' . $name;
    }

    private function buildSocialJsonFromPost(): string
    {
        /** @var mixed $labels */
        $labels = $_POST['social_label'] ?? [];
        /** @var mixed $urls */
        $urls = $_POST['social_url'] ?? [];
        if (!is_array($labels) || !is_array($urls)) {
            return '';
        }

        $out = [];
        $n = max(count($labels), count($urls));
        for ($i = 0; $i < $n; $i++) {
            $label = trim((string) ($labels[$i] ?? ''));
            $url = trim((string) ($urls[$i] ?? ''));
            if ($label === '' || $url === '') {
                continue;
            }
            $out[] = ['label' => $label, 'url' => $url];
        }

        return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function respondSave(bool $ok, string $message, int $code = 200): void
    {
        $ajax = strtolower((string) Request::header('X-Requested-With', '')) === 'xmlhttprequest'
            || (string) Request::post('ajax', '') === '1';

        if ($ajax) {
            http_response_code($ok ? 200 : $code);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($ok) {
            $_SESSION['flash_success'] = $message;
        } else {
            $_SESSION['flash_error'] = $message;
        }
        $this->redirect('/admin/settings');
    }
}
