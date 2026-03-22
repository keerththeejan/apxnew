<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\FooterGallery;

final class FooterGalleryController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/footer-gallery');
    }

    public function index(): void
    {
        $this->requireAuth();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $schemaReady = true;
        try {
            $items = FooterGallery::allOrdered();
        } catch (\Throwable) {
            $items = [];
            $schemaReady = false;
        }

        $maxSort = 0;
        foreach ($items as $row) {
            $maxSort = max($maxSort, (int) ($row['sort_order'] ?? 0));
        }

        view('admin.footer_gallery', [
            'title' => 'APX Admin - Footer gallery',
            'pageKey' => 'footer_gallery',
            'pageTitle' => 'Footer gallery',
            'crumb' => 'APX / Footer gallery',
            'items' => $items,
            'schemaReady' => $schemaReady,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'nextSortOrder' => $maxSort + 1,
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

        $upload = $this->handleGalleryUpload();
        if ($upload === false) {
            $_SESSION['flash_error'] = 'Image upload failed (invalid type or size; max 3 MB).';
            $this->redirect('/admin/footer-gallery');

            return;
        }
        if ($upload === null) {
            $_SESSION['flash_error'] = 'Please choose an image.';
            $this->redirect('/admin/footer-gallery');

            return;
        }

        $data = [
            'image_path' => $upload,
            'alt_text' => mb_substr(trim((string) Request::post('alt_text', '')), 0, 220),
            'sort_order' => (int) Request::post('sort_order', 0),
            'is_active' => (int) Request::post('is_active', 0) === 1 ? 1 : 0,
        ];

        try {
            FooterGallery::create($data);
            $_SESSION['flash_success'] = 'Gallery image added.';
        } catch (\Throwable) {
            $_SESSION['flash_error'] = 'Could not save. Run migrations if the table is missing.';
        }

        $this->redirect('/admin/footer-gallery');
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
            $this->redirect('/admin/footer-gallery');

            return;
        }

        $existing = FooterGallery::findById($id);
        if ($existing === null) {
            $_SESSION['flash_error'] = 'Item not found.';
            $this->redirect('/admin/footer-gallery');

            return;
        }

        $upload = $this->handleGalleryUpload();
        if ($upload === false) {
            $_SESSION['flash_error'] = 'Image upload failed (invalid type or size; max 3 MB).';
            $this->redirect('/admin/footer-gallery');

            return;
        }

        $clearImage = (int) Request::post('clear_image', 0) === 1;
        $path = (string) ($existing['image_path'] ?? '');
        if ($clearImage) {
            $this->deleteManagedFile($path);
            $path = '';
        } elseif ($upload !== null) {
            $this->deleteManagedFile($path);
            $path = $upload;
        }

        if ($path === '') {
            $_SESSION['flash_error'] = 'Gallery item must have an image. Upload a new file or do not clear the image.';
            $this->redirect('/admin/footer-gallery');

            return;
        }

        $data = [
            'image_path' => $path,
            'alt_text' => mb_substr(trim((string) Request::post('alt_text', '')), 0, 220),
            'sort_order' => (int) Request::post('sort_order', 0),
            'is_active' => (int) Request::post('is_active', 0) === 1 ? 1 : 0,
        ];

        try {
            FooterGallery::update($id, $data);
            $_SESSION['flash_success'] = 'Gallery image updated.';
        } catch (\Throwable) {
            $_SESSION['flash_error'] = 'Could not update.';
        }

        $this->redirect('/admin/footer-gallery');
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
            $this->redirect('/admin/footer-gallery');

            return;
        }

        $existing = FooterGallery::findById($id);
        if ($existing !== null) {
            $this->deleteManagedFile((string) ($existing['image_path'] ?? ''));
            try {
                FooterGallery::delete($id);
                $_SESSION['flash_success'] = 'Gallery image removed.';
            } catch (\Throwable) {
                $_SESSION['flash_error'] = 'Could not delete.';
            }
        }

        $this->redirect('/admin/footer-gallery');
    }

    /**
     * @return null|string|false null = no file; string = path; false = error
     */
    private function handleGalleryUpload()
    {
        if (!isset($_FILES['gallery_image']) || !is_array($_FILES['gallery_image'])) {
            return null;
        }
        $f = $_FILES['gallery_image'];
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

        $dir = dirname(__DIR__, 3) . '/public/uploads/footer-gallery';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $name = 'fg-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
        $dest = $dir . DIRECTORY_SEPARATOR . $name;
        if (!@move_uploaded_file($f['tmp_name'], $dest)) {
            return false;
        }

        return '/uploads/footer-gallery/' . $name;
    }

    private function deleteManagedFile(string $webPath): void
    {
        $webPath = trim($webPath);
        if ($webPath === '' || !str_starts_with($webPath, '/uploads/footer-gallery/')) {
            return;
        }
        $full = dirname(__DIR__, 3) . '/public' . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
