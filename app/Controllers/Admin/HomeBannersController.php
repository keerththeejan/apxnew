<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\HomeBanner;

final class HomeBannersController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/banners');
    }

    public function index(): void
    {
        $this->requireAuth();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $schemaReady = true;
        try {
            $items = HomeBanner::allOrdered();
        } catch (\Throwable) {
            $items = [];
            $schemaReady = false;
        }

        $maxOrder = 0;
        foreach ($items as $row) {
            $maxOrder = max($maxOrder, (int) ($row['order_index'] ?? 0));
        }

        view('admin.home_banners', [
            'title' => 'APX Admin - Home banners',
            'pageKey' => 'banners',
            'pageTitle' => 'Home banners',
            'crumb' => 'APX / Home banners',
            'items' => $items,
            'schemaReady' => $schemaReady,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'nextOrderIndex' => $maxOrder + 1,
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

        $upload = $this->handleBannerUpload();
        if ($upload === false) {
            $_SESSION['flash_error'] = 'Image upload failed (invalid type or size; max 3 MB).';

            $this->redirect('/admin/banners');

            return;
        }

        $data = $this->sanitizeBannerPost();
        if ($upload !== null) {
            $data['image_path'] = $upload;
        }

        try {
            HomeBanner::create($data);
            $_SESSION['flash_success'] = 'Banner created.';
        } catch (\Throwable) {
            $_SESSION['flash_error'] = 'Could not save banner. Run database migrations if the table is missing.';
        }

        $this->redirect('/admin/banners');
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
            $this->redirect('/admin/banners');

            return;
        }

        $existing = HomeBanner::findById($id);
        if ($existing === null) {
            $_SESSION['flash_error'] = 'Banner not found.';
            $this->redirect('/admin/banners');

            return;
        }

        $clearImage = (int) Request::post('clear_image', 0) === 1;
        $upload = $this->handleBannerUpload();
        if ($upload === false) {
            $_SESSION['flash_error'] = 'Image upload failed (invalid type or size; max 3 MB).';

            $this->redirect('/admin/banners');

            return;
        }

        $data = $this->sanitizeBannerPost();
        if ($clearImage) {
            $this->deletePublicFileIfManaged((string) ($existing['image_path'] ?? ''));
            $data['image_path'] = null;
        } elseif ($upload !== null) {
            $this->deletePublicFileIfManaged((string) ($existing['image_path'] ?? ''));
            $data['image_path'] = $upload;
        } else {
            $data['image_path'] = (string) ($existing['image_path'] ?? '');
        }

        try {
            HomeBanner::update($id, $data);
            $_SESSION['flash_success'] = 'Banner updated.';
        } catch (\Throwable) {
            $_SESSION['flash_error'] = 'Could not update banner.';
        }

        $this->redirect('/admin/banners');
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
            $this->redirect('/admin/banners');

            return;
        }

        $existing = HomeBanner::findById($id);
        if ($existing !== null) {
            $this->deletePublicFileIfManaged((string) ($existing['image_path'] ?? ''));
            try {
                HomeBanner::deleteById($id);
                $_SESSION['flash_success'] = 'Banner deleted.';
            } catch (\Throwable) {
                $_SESSION['flash_error'] = 'Could not delete banner.';
            }
        }

        $this->redirect('/admin/banners');
    }

    /** @return array<string, mixed> */
    private function sanitizeBannerPost(): array
    {
        return [
            'title' => mb_substr(trim((string) Request::post('title', '')), 0, 220),
            'subtitle' => mb_substr(trim((string) Request::post('subtitle', '')), 0, 500),
            'show_image' => (int) Request::post('show_image', 0) === 1 ? 1 : 0,
            'button1_text' => mb_substr(trim((string) Request::post('button1_text', '')), 0, 120),
            'button1_link' => mb_substr(trim((string) Request::post('button1_link', '')), 0, 500),
            'button2_text' => mb_substr(trim((string) Request::post('button2_text', '')), 0, 120),
            'button2_link' => mb_substr(trim((string) Request::post('button2_link', '')), 0, 500),
            'order_index' => (int) Request::post('order_index', 0),
            'is_active' => (int) Request::post('is_active', 1),
        ];
    }

    /**
     * @return null|string|false null = no file; string = path; false = error
     */
    private function handleBannerUpload()
    {
        if (!isset($_FILES['banner_image']) || !is_array($_FILES['banner_image'])) {
            return null;
        }
        $f = $_FILES['banner_image'];
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

        $dir = dirname(__DIR__, 3) . '/public/uploads/banners';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $name = 'banner-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (!@move_uploaded_file($f['tmp_name'], $dest)) {
            return false;
        }

        return '/uploads/banners/' . $name;
    }

    private function deletePublicFileIfManaged(string $webPath): void
    {
        $webPath = trim($webPath);
        if ($webPath === '' || !str_starts_with($webPath, '/uploads/banners/')) {
            return;
        }
        $full = dirname(__DIR__, 3) . '/public' . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
