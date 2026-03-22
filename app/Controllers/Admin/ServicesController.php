<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Service;

final class ServicesController extends AdminBaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 12);
        $result = Service::paginate($q, $page, $perPage);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.services', [
            'title' => 'APX Admin - Services',
            'pageKey' => 'services',
            'pageTitle' => 'Services',
            'crumb' => 'APX / Services',
            'q' => $q,
            'services' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'pageCount' => $result['pageCount'],
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
            'icon' => trim((string) Request::post('icon', '')),
            'title' => trim((string) Request::post('title', '')),
            'description' => trim((string) Request::post('description', '')),
            'link' => trim((string) Request::post('link', '')),
            'sort_order' => (int) Request::post('sort_order', 0),
            'is_active' => (int) Request::post('is_active', 1),
        ];
        if ($data['title'] === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            $this->redirect('/admin/services');
            return;
        }

        $id = Service::create($data);
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'service.create', 'service', $id, null);
        } catch (\Throwable $e) {
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
        ];
        if ($data['title'] === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            $this->redirect('/admin/services');
            return;
        }

        Service::update($id, $data);
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'service.update', 'service', $id, null);
        } catch (\Throwable $e) {
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

        Service::delete($id);
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'service.delete', 'service', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Service deleted.';
        $this->redirect('/admin/services');
    }
}
