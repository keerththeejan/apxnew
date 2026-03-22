<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\Page;

final class PagesController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/pages');
    }

    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 10);
        $sort = (string) Request::get('sort', 'updated_at');
        $dir = (string) Request::get('dir', 'DESC');

        $result = Page::paginate($q, $page, $perPage, $sort, $dir);

        $flashErrors = $_SESSION['flash_errors'] ?? [];
        $flashOld = $_SESSION['flash_old'] ?? [];
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_errors'], $_SESSION['flash_old'], $_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.pages', [
            'q' => $q,
            'pages' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'pageCount' => $result['pageCount'],
            'sort' => $sort,
            'dir' => strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC',
            'flashErrors' => $flashErrors,
            'flashOld' => $flashOld,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
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
            'key' => trim((string) Request::post('key', '')),
            'title' => trim((string) Request::post('title', '')),
            'slug' => trim((string) Request::post('slug', '')),
            'content' => (string) Request::post('content', ''),
            'is_active' => (int) Request::post('is_active', 1),
        ];

        $errors = [];
        if ($data['key'] === '') { $errors['key'][] = 'Key is required.'; }
        if ($data['title'] === '') { $errors['title'][] = 'Title is required.'; }

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['flash_old'] = $data;
            $this->redirect('/admin/pages');
            return;
        }

        try {
            Page::create($data, (int) ($_SESSION['admin_id'] ?? 0));
            $_SESSION['flash_success'] = 'Page created.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to create page.';
            $_SESSION['flash_old'] = $data;
        }

        $this->redirect('/admin/pages');
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
            $_SESSION['flash_error'] = 'Invalid page id.';
            $this->redirect('/admin/pages');
            return;
        }

        $data = [
            'key' => trim((string) Request::post('key', '')),
            'title' => trim((string) Request::post('title', '')),
            'slug' => trim((string) Request::post('slug', '')),
            'content' => (string) Request::post('content', ''),
            'is_active' => (int) Request::post('is_active', 1),
        ];

        $errors = [];
        if ($data['key'] === '') { $errors['key'][] = 'Key is required.'; }
        if ($data['title'] === '') { $errors['title'][] = 'Title is required.'; }

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['flash_old'] = $data + ['id' => $id];
            $this->redirect('/admin/pages');
            return;
        }

        try {
            Page::update($id, $data, (int) ($_SESSION['admin_id'] ?? 0));
            $_SESSION['flash_success'] = 'Page updated.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to update page.';
            $_SESSION['flash_old'] = $data + ['id' => $id];
        }

        $this->redirect('/admin/pages');
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
            $_SESSION['flash_error'] = 'Invalid page id.';
            $this->redirect('/admin/pages');
            return;
        }

        try {
            $deleted = Page::delete($id);
            $_SESSION['flash_success'] = $deleted ? 'Page deleted.' : 'Nothing deleted.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to delete page.';
        }

        $this->redirect('/admin/pages');
    }
}
