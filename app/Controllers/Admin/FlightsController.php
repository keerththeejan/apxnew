<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\Flight;

final class FlightsController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/flights');
    }

    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 10);
        $sort = (string) Request::get('sort', 'updated_at');
        $dir = (string) Request::get('dir', 'DESC');

        $result = Flight::paginate($q, $page, $perPage, $sort, $dir);

        $flashErrors = $_SESSION['flash_errors'] ?? [];
        $flashOld = $_SESSION['flash_old'] ?? [];
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_errors'], $_SESSION['flash_old'], $_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.flights', [
            'q' => $q,
            'flights' => $result['rows'],
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
            'title' => trim((string) Request::post('title', '')),
            'summary' => trim((string) Request::post('summary', '')),
            'origin' => trim((string) Request::post('origin', '')),
            'destination' => trim((string) Request::post('destination', '')),
            'price_from' => trim((string) Request::post('price_from', '')),
            'is_deal' => (int) Request::post('is_deal', 0),
            'is_active' => (int) Request::post('is_active', 0),
        ];

        $errors = [];
        if ($data['title'] === '') { $errors['title'][] = 'Title is required.'; }

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['flash_old'] = $data;
            $this->redirect('/admin/flights');
            return;
        }

        try {
            Flight::create($data);
            $_SESSION['flash_success'] = 'Flight created.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to create flight.';
            $_SESSION['flash_old'] = $data;
        }

        $this->redirect('/admin/flights');
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
            $_SESSION['flash_error'] = 'Invalid flight id.';
            $this->redirect('/admin/flights');
            return;
        }

        $data = [
            'title' => trim((string) Request::post('title', '')),
            'summary' => trim((string) Request::post('summary', '')),
            'origin' => trim((string) Request::post('origin', '')),
            'destination' => trim((string) Request::post('destination', '')),
            'price_from' => trim((string) Request::post('price_from', '')),
            'is_deal' => (int) Request::post('is_deal', 0),
            'is_active' => (int) Request::post('is_active', 0),
        ];

        $errors = [];
        if ($data['title'] === '') { $errors['title'][] = 'Title is required.'; }

        if ($errors) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['flash_old'] = $data + ['id' => $id];
            $this->redirect('/admin/flights');
            return;
        }

        try {
            Flight::update($id, $data);
            $_SESSION['flash_success'] = 'Flight updated.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to update flight.';
            $_SESSION['flash_old'] = $data + ['id' => $id];
        }

        $this->redirect('/admin/flights');
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
            $_SESSION['flash_error'] = 'Invalid flight id.';
            $this->redirect('/admin/flights');
            return;
        }

        try {
            $deleted = Flight::delete($id);
            $_SESSION['flash_success'] = $deleted ? 'Flight deleted.' : 'Nothing deleted.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to delete flight.';
        }

        $this->redirect('/admin/flights');
    }
}
