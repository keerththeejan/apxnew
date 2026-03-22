<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Destination;
use App\Models\Hotel;
use PDOException;

final class HotelsController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/hotels');
    }

    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 12);
        $result = Hotel::paginate($q, $page, $perPage);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $s = \App\Models\Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');

        view('admin.hotels', [
            'title' => 'APX Admin - Hotels',
            'pageKey' => 'hotels',
            'pageTitle' => 'Hotel Bookings',
            'crumb' => $siteName . ' / Hotels',
            'q' => $q,
            'items' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'pageCount' => $result['pageCount'],
            'destinations' => Destination::allActive(),
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

        $data = $this->payloadFromRequest();
        if ($data['name'] === '') {
            $_SESSION['flash_error'] = 'Hotel name is required.';
            $this->redirect('/admin/hotels');
            return;
        }

        try {
            $id = Hotel::create($data);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = $this->missingTableMessage();
                $this->redirect('/admin/hotels');
                return;
            }
            throw $e;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'hotel.create', 'hotel', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Hotel saved.';
        $this->redirect('/admin/hotels');
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
            $this->redirect('/admin/hotels');
            return;
        }

        $data = $this->payloadFromRequest();
        if ($data['name'] === '') {
            $_SESSION['flash_error'] = 'Hotel name is required.';
            $this->redirect('/admin/hotels');
            return;
        }

        try {
            Hotel::update($id, $data);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = $this->missingTableMessage();
                $this->redirect('/admin/hotels');
                return;
            }
            throw $e;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'hotel.update', 'hotel', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Hotel updated.';
        $this->redirect('/admin/hotels');
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
            $this->redirect('/admin/hotels');
            return;
        }

        try {
            Hotel::delete($id);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = $this->missingTableMessage();
                $this->redirect('/admin/hotels');
                return;
            }
            throw $e;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'hotel.delete', 'hotel', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Hotel removed.';
        $this->redirect('/admin/hotels');
    }

    /**
     * @return array{name:string,city:string,country:string,price_from:string,is_featured:int,is_active:int,destination_id:int|null}
     */
    private function payloadFromRequest(): array
    {
        $dest = trim((string) Request::post('destination_id', ''));

        return [
            'name' => trim((string) Request::post('name', '')),
            'city' => trim((string) Request::post('city', '')),
            'country' => trim((string) Request::post('country', '')),
            'price_from' => trim((string) Request::post('price_from', '')),
            'is_featured' => (int) Request::post('is_featured', 0),
            'is_active' => (int) Request::post('is_active', 1),
            'destination_id' => $dest === '' ? null : (int) $dest,
        ];
    }

    private function isMissingTable(PDOException $e): bool
    {
        $m = $e->getMessage();
        return str_contains($m, '42S02') || str_contains($m, "doesn't exist");
    }

    private function missingTableMessage(): string
    {
        return 'The hotels table is missing. Import database/schema.sql or run migrations.';
    }
}
