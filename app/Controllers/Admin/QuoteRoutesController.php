<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\QuoteRoute;
use App\Models\Setting;
use PDOException;

final class QuoteRoutesController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/quotes');
    }

    public function index(): void
    {
        $this->requireAuth();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $s = Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');

        view('admin.quotes', [
            'title' => 'APX Admin - Quote management',
            'pageKey' => 'quotes',
            'pageTitle' => 'Quote management',
            'crumb' => $siteName . ' / Quotes',
            'items' => QuoteRoute::schemaReady() ? QuoteRoute::adminAll() : [],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'schemaReady' => QuoteRoute::schemaReady(),
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
            'slug' => trim((string) Request::post('slug', '')),
            'label' => trim((string) Request::post('label', '')),
            'country' => trim((string) Request::post('country', '')),
            'service' => trim((string) Request::post('service', '')),
            'price_per_kg' => Request::post('price_per_kg', '0'),
            'sort_order' => (int) Request::post('sort_order', 0),
            'is_active' => (int) Request::post('is_active', 1),
        ];

        if ($data['country'] === '' || $data['service'] === '') {
            $_SESSION['flash_error'] = 'Country and service are required.';
            $this->redirect('/admin/quotes');

            return;
        }

        try {
            $id = QuoteRoute::create($data);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = 'Run database migrations to create the quote_routes table.';
                $this->redirect('/admin/quotes');

                return;
            }
            throw $e;
        }

        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'quote_route.create', 'quote_route', $id, null);
        } catch (\Throwable) {
        }

        $_SESSION['flash_success'] = 'Quote route saved.';
        $this->redirect('/admin/quotes');
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
            $_SESSION['flash_error'] = 'Invalid route.';
            $this->redirect('/admin/quotes');

            return;
        }

        $data = [
            'slug' => trim((string) Request::post('slug', '')),
            'label' => trim((string) Request::post('label', '')),
            'country' => trim((string) Request::post('country', '')),
            'service' => trim((string) Request::post('service', '')),
            'price_per_kg' => Request::post('price_per_kg', '0'),
            'sort_order' => (int) Request::post('sort_order', 0),
            'is_active' => (int) Request::post('is_active', 1),
        ];

        if ($data['country'] === '' || $data['service'] === '') {
            $_SESSION['flash_error'] = 'Country and service are required.';
            $this->redirect('/admin/quotes');

            return;
        }

        try {
            QuoteRoute::update($id, $data);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = 'Run database migrations to create the quote_routes table.';
                $this->redirect('/admin/quotes');

                return;
            }
            throw $e;
        }

        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'quote_route.update', 'quote_route', $id, null);
        } catch (\Throwable) {
        }

        $_SESSION['flash_success'] = 'Quote route updated.';
        $this->redirect('/admin/quotes');
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
            $_SESSION['flash_error'] = 'Invalid route.';
            $this->redirect('/admin/quotes');

            return;
        }

        try {
            QuoteRoute::delete($id);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = 'Run database migrations to create the quote_routes table.';
                $this->redirect('/admin/quotes');

                return;
            }
            throw $e;
        }

        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'quote_route.delete', 'quote_route', $id, null);
        } catch (\Throwable) {
        }

        $_SESSION['flash_success'] = 'Quote route deleted.';
        $this->redirect('/admin/quotes');
    }

    private function isMissingTable(PDOException $e): bool
    {
        $m = $e->getMessage();

        return str_contains($m, '42S02') || str_contains($m, "doesn't exist");
    }
}
