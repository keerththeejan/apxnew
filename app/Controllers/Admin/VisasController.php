<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\Application;
use App\Models\Destination;
use App\Models\Visa;

final class VisasController extends AdminBaseController
{
    /** Server-side alias: /admin/visa.html → /admin/visa (avoids static HTML shim). */
    public function htmlAlias(): void
    {
        $this->redirect('/admin/visa');
    }

    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 12);
        $visas = Visa::paginate($q, $page, $perPage);

        $appQ = trim((string) Request::get('app_q', ''));
        $appStatus = trim((string) Request::get('app_status', 'all'));
        $appPage = (int) Request::get('app_page', 1);
        $apps = Application::paginateVisaRelated($appQ, $appStatus, $appPage, 10);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.visa', [
            'title' => 'APX Admin - Visa Services',
            'pageKey' => 'visa',
            'pageTitle' => 'Visa Services',
            'crumb' => 'APX / Visa Services',
            'q' => $q,
            'visas' => $visas['rows'],
            'total' => $visas['total'],
            'page' => $visas['page'],
            'perPage' => $visas['perPage'],
            'pageCount' => $visas['pageCount'],
            'app_q' => $appQ,
            'app_status' => $appStatus,
            'applications' => $apps['rows'],
            'appTotal' => $apps['total'],
            'appPage' => $apps['page'],
            'appPageCount' => $apps['pageCount'],
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

        $data = $this->visaPayloadFromRequest();
        if ($data['title'] === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            $this->redirect('/admin/visa');
            return;
        }

        Visa::create($data);
        $_SESSION['flash_success'] = 'Visa service created.';
        $this->redirect('/admin/visa');
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
            $this->redirect('/admin/visa');
            return;
        }

        $data = $this->visaPayloadFromRequest();
        if ($data['title'] === '') {
            $_SESSION['flash_error'] = 'Title is required.';
            $this->redirect('/admin/visa');
            return;
        }

        Visa::update($id, $data);
        $_SESSION['flash_success'] = 'Visa service updated.';
        $this->redirect('/admin/visa');
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
            $this->redirect('/admin/visa');
            return;
        }

        Visa::delete($id);
        $_SESSION['flash_success'] = 'Visa service deleted.';
        $this->redirect('/admin/visa');
    }

    /**
     * @return array{title:string,summary:string,requirements:string,processing_days:mixed,fee_from:mixed,destination_id:mixed,is_active:int}
     */
    private function visaPayloadFromRequest(): array
    {
        $dest = trim((string) Request::post('destination_id', ''));
        $pd = trim((string) Request::post('processing_days', ''));
        $fee = trim((string) Request::post('fee_from', ''));

        return [
            'title' => trim((string) Request::post('title', '')),
            'summary' => trim((string) Request::post('summary', '')),
            'requirements' => trim((string) Request::post('requirements', '')),
            'processing_days' => $pd === '' ? null : (int) $pd,
            'fee_from' => $fee === '' ? null : $fee,
            'destination_id' => $dest === '' ? null : (int) $dest,
            'is_active' => (int) Request::post('is_active', 1),
        ];
    }
}
