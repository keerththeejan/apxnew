<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use PDOException;

final class EnquiriesController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/enquiries');
    }

    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $filter = trim((string) Request::get('filter', 'all'));
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 15);
        $result = ContactMessage::paginate($q, $filter, $page, $perPage);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $s = \App\Models\Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');

        view('admin.enquiries', [
            'title' => 'APX Admin - Customer Enquiries',
            'pageKey' => 'enquiries',
            'pageTitle' => 'Customer Enquiries',
            'crumb' => $siteName . ' / Enquiries',
            'q' => $q,
            'filter' => $filter,
            'messages' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ]);
    }

    public function markRead(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid message.';
            $this->redirect('/admin/enquiries');
            return;
        }

        try {
            ContactMessage::markRead($id);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = $this->missingTableMessage();
                $this->redirect('/admin/enquiries');
                return;
            }
            throw $e;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'contact_message.read', 'contact_message', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Marked as read.';
        $this->redirectBack();
    }

    public function markUnread(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid message.';
            $this->redirect('/admin/enquiries');
            return;
        }

        try {
            ContactMessage::markUnread($id);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = $this->missingTableMessage();
                $this->redirect('/admin/enquiries');
                return;
            }
            throw $e;
        }
        $_SESSION['flash_success'] = 'Marked as new.';
        $this->redirectBack();
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
            $_SESSION['flash_error'] = 'Invalid message.';
            $this->redirect('/admin/enquiries');
            return;
        }

        try {
            ContactMessage::delete($id);
        } catch (PDOException $e) {
            if ($this->isMissingTable($e)) {
                $_SESSION['flash_error'] = $this->missingTableMessage();
                $this->redirect('/admin/enquiries');
                return;
            }
            throw $e;
        }
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'contact_message.delete', 'contact_message', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Message deleted.';
        $this->redirectBack();
    }

    private function redirectBack(): void
    {
        $q = trim((string) Request::post('return_q', ''));
        $filter = trim((string) Request::post('return_filter', 'all'));
        $page = (int) Request::post('return_page', 1);
        $parts = [];
        if ($q !== '') {
            $parts[] = 'q=' . rawurlencode($q);
        }
        if ($filter !== '' && $filter !== 'all') {
            $parts[] = 'filter=' . rawurlencode($filter);
        }
        if ($page > 1) {
            $parts[] = 'page=' . $page;
        }
        $this->redirect('/admin/enquiries' . ($parts !== [] ? '?' . implode('&', $parts) : ''));
    }

    private function isMissingTable(PDOException $e): bool
    {
        $m = $e->getMessage();
        return str_contains($m, '42S02') || str_contains($m, "doesn't exist");
    }

    private function missingTableMessage(): string
    {
        return 'The contact_messages table is missing. Import database/schema.sql.';
    }
}
