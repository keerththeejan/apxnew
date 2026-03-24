<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Service;
use App\Services\CountryCatalog;
use App\Services\SiteConfig;
use App\Services\WhatsAppService;

final class ServicesController extends AdminBaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $country = strtoupper(trim((string) Request::get('country', '')));
        if ($country !== '' && !preg_match('/^[A-Z]{2}$/', $country)) {
            $country = '';
        }
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 12);
        $result = Service::paginate($q, $page, $perPage, $country);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $extraHead = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" crossorigin="anonymous" />';

        view('admin.services', [
            'title' => 'APX Admin - Services',
            'pageKey' => 'services',
            'pageTitle' => 'Services',
            'crumb' => 'APX / Services',
            'q' => $q,
            'countryFilter' => $country,
            'countryCodesInUse' => Service::distinctCountryCodes(),
            'countriesJson' => CountryCatalog::all(),
            'services' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'servicesImageColumn' => Service::hasImagePathColumn(),
            'servicesCountryColumn' => Service::hasCountryCodeColumn(),
            'whatsappNumber' => SiteConfig::get('whatsapp_number', ''),
            'extraHead' => $extraHead,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $data = [
            'icon' => trim((string) Request::post('icon', '')),
            'title' => trim((string) Request::post('title', '')),
            'description' => trim((string) Request::post('description', '')),
            'link' => trim((string) Request::post('link', '')),
            'sort_order' => (int) Request::post('sort_order', 0),
            'is_active' => (int) Request::post('is_active', 1),
            'country_code' => CountryCatalog::normalize((string) Request::post('country_code', '')),
        ];
        if ($data['title'] === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            $this->redirect('/admin/services');
            return;
        }

        $data['image_path'] = '';
        if (Service::hasImagePathColumn()) {
            $upload = $this->handleServiceImageUpload();
            if ($upload === false) {
                $_SESSION['flash_error'] = 'Image upload failed (invalid type or size; max 3 MB).';
                $this->redirect('/admin/services');

                return;
            }
            $data['image_path'] = $upload ?? '';
        }

        try {
            $id = Service::create($data);
        } catch (\Throwable) {
            if (($data['image_path'] ?? '') !== '') {
                $this->deleteManagedServiceFile((string) $data['image_path']);
            }
            $_SESSION['flash_error'] = 'Could not save. Check the database connection and services table.';
            $this->redirect('/admin/services');

            return;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'service.create', 'service', $id, null);
        } catch (\Throwable $e) {
        }
        try {
            $phone = SiteConfig::get('whatsapp_number', '');
            if ($phone !== '') {
                $msg = WhatsAppService::renderTemplate('whatsapp_tpl_service_info', [
                    'name' => 'Admin',
                    'service' => (string) $data['title'],
                    'status' => 'created',
                ]);
                WhatsAppService::sendText($phone, $msg, 'service.created', $id);
            }
        } catch (\Throwable) {
        }
        $_SESSION['flash_success'] = 'Service created.';
        $this->redirect('/admin/services');
    }

    public function update(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid id.';
            $this->redirect('/admin/services');
            return;
        }

        $data = [
            'icon' => trim((string) Request::post('icon', '')),
            'title' => trim((string) Request::post('title', '')),
            'description' => trim((string) Request::post('description', '')),
            'link' => trim((string) Request::post('link', '')),
            'sort_order' => (int) Request::post('sort_order', 0),
            'is_active' => (int) Request::post('is_active', 1),
            'country_code' => CountryCatalog::normalize((string) Request::post('country_code', '')),
        ];
        if ($data['title'] === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            $this->redirect('/admin/services');
            return;
        }

        $existing = Service::findById($id);
        if ($existing === null) {
            $_SESSION['flash_error'] = 'Service not found.';
            $this->redirect('/admin/services');

            return;
        }

        if (Service::hasImagePathColumn()) {
            $upload = $this->handleServiceImageUpload();
            if ($upload === false) {
                $_SESSION['flash_error'] = 'Image upload failed (invalid type or size; max 3 MB).';
                $this->redirect('/admin/services');

                return;
            }

            $currentPath = (string) ($existing['image_path'] ?? '');
            $newPath = $currentPath;
            if (Request::post('clear_image') === '1') {
                $this->deleteManagedServiceFile($currentPath);
                $newPath = '';
            } elseif ($upload !== null) {
                $this->deleteManagedServiceFile($currentPath);
                $newPath = $upload;
            }
            $data['image_path'] = $newPath;
        }

        try {
            Service::update($id, $data);
        } catch (\Throwable) {
            if (Service::hasImagePathColumn()) {
                $np = (string) ($data['image_path'] ?? '');
                $cp = (string) ($existing['image_path'] ?? '');
                if ($np !== '' && $np !== $cp) {
                    $this->deleteManagedServiceFile($np);
                }
            }
            $_SESSION['flash_error'] = 'Could not update. Check the database connection and services table.';
            $this->redirect('/admin/services');

            return;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'service.update', 'service', $id, null);
        } catch (\Throwable $e) {
        }
        try {
            $phone = SiteConfig::get('whatsapp_number', '');
            if ($phone !== '') {
                $msg = WhatsAppService::renderTemplate('whatsapp_tpl_service_info', [
                    'name' => 'Admin',
                    'service' => (string) $data['title'],
                    'status' => 'updated',
                ]);
                WhatsAppService::sendText($phone, $msg, 'service.updated', $id);
            }
        } catch (\Throwable) {
        }
        $_SESSION['flash_success'] = 'Service updated.';
        $this->redirect('/admin/services');
    }

    public function destroy(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid id.';
            $this->redirect('/admin/services');
            return;
        }

        $row = Service::findById($id);
        if ($row !== null) {
            $this->deleteManagedServiceFile((string) ($row['image_path'] ?? ''));
        }
        Service::delete($id);
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'service.delete', 'service', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Service deleted.';
        $this->redirect('/admin/services');
    }

    /**
     * @return null|string|false null = no file uploaded; string = web path; false = invalid upload
     */
    private function handleServiceImageUpload()
    {
        if (!isset($_FILES['service_image']) || !is_array($_FILES['service_image'])) {
            return null;
        }
        $f = $_FILES['service_image'];
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($f['error'] ?? 0) !== UPLOAD_ERR_OK || !isset($f['tmp_name']) || !is_uploaded_file($f['tmp_name'])) {
            return false;
        }
        if (($f['size'] ?? 0) > 3_145_728) {
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

        $dir = dirname(__DIR__, 3) . '/public/uploads/services';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $name = 'svc-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (!@move_uploaded_file($f['tmp_name'], $dest)) {
            return false;
        }

        return '/uploads/services/' . $name;
    }

    private function deleteManagedServiceFile(string $webPath): void
    {
        $webPath = trim($webPath);
        if ($webPath === '' || !str_starts_with($webPath, '/uploads/services/')) {
            return;
        }
        $full = dirname(__DIR__, 3) . '/public' . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
