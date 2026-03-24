<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\AdminNotification;
use App\Models\Setting;
use App\Models\WhatsAppLog;
use App\Services\WhatsAppService;

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

    public function whatsapp(): void
    {
        $this->requireSuperAdmin();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $s = Setting::allKeyed();
        view('admin.settings_whatsapp', [
            'title' => 'APX Admin - WhatsApp Settings',
            'pageKey' => 'settings',
            'pageTitle' => 'WhatsApp Settings',
            'crumb' => 'APX / Settings / WhatsApp',
            'settings' => $s,
            'logs' => WhatsAppLog::latest(120),
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

    public function saveWhatsapp(): void
    {
        $this->requireSuperAdmin();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            $this->respondSave(false, 'CSRF token mismatch', 419, '/admin/settings/whatsapp');

            return;
        }

        $enabled = Request::post('whatsapp_enabled') === '1' ? '1' : '0';
        $countryCode = preg_replace('/\D/', '', (string) Request::post('whatsapp_country_code', '94')) ?? '94';
        $countryCode = $countryCode !== '' ? $countryCode : '94';

        $num = WhatsAppService::formatPhone((string) Request::post('whatsapp_number', ''), $countryCode);
        if ((string) Request::post('whatsapp_number', '') !== '' && $num === '') {
            $this->respondSave(false, 'Invalid WhatsApp number format.', 422, '/admin/settings/whatsapp');

            return;
        }

        Setting::set('whatsapp_enabled', $enabled);
        Setting::set('whatsapp_country_code', $countryCode);
        Setting::set('whatsapp_number', $num);
        Setting::set('whatsapp_phone_number_id', trim((string) Request::post('whatsapp_phone_number_id', '')));
        Setting::set('whatsapp_api_token', trim((string) Request::post('whatsapp_api_token', '')));
        Setting::set('whatsapp_tpl_new_order', trim((string) Request::post('whatsapp_tpl_new_order', '')));
        Setting::set('whatsapp_tpl_status_update', trim((string) Request::post('whatsapp_tpl_status_update', '')));
        Setting::set('whatsapp_tpl_service_info', trim((string) Request::post('whatsapp_tpl_service_info', '')));

        try {
            ActivityLog::record(
                isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null,
                'settings.whatsapp.save',
                'settings',
                null,
                ['keys' => 'whatsapp']
            );
            AdminNotification::create('WhatsApp settings were updated.', 'info');
        } catch (\Throwable) {
        }

        $this->respondSave(true, 'WhatsApp settings saved.', 200, '/admin/settings/whatsapp');
    }

    public function sendWhatsapp(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => 'CSRF token mismatch'], JSON_UNESCAPED_UNICODE);

            return;
        }

        $phone = trim((string) Request::post('phone', ''));
        $msg = trim((string) Request::post('message', ''));
        if ($phone === '' || $msg === '') {
            http_response_code(422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'message' => 'Phone and message are required'], JSON_UNESCAPED_UNICODE);

            return;
        }

        $ctx = trim((string) Request::post('context', 'admin.manual'));
        $entityId = (int) Request::post('entity_id', 0);
        $res = WhatsAppService::sendText($phone, $msg, $ctx, $entityId > 0 ? $entityId : null);

        header('Content-Type: application/json; charset=utf-8');
        http_response_code($res['ok'] ? 200 : 422);
        echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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

    private function respondSave(bool $ok, string $message, int $code = 200, string $redirectTo = '/admin/settings'): void
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
        $this->redirect($redirectTo);
    }
}
