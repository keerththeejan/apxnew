<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\NavItem;
use Throwable;

final class NavigationController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/navigation');
    }

    public function index(): void
    {
        $this->requireAuth();

        $flashErrors = $_SESSION['flash_errors'] ?? [];
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_errors'], $_SESSION['flash_success'], $_SESSION['flash_error']);

        $items = NavItem::allOrdered();
        $menuTree = NavItem::adminMenuTree();
        $byId = [];
        foreach ($items as $row) {
            $byId[(int) ($row['id'] ?? 0)] = $row;
        }

        view('admin.navigation', [
            'items' => $items,
            'menuTree' => $menuTree,
            'itemsById' => $byId,
            'flashErrors' => $flashErrors,
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
        $errors = $this->validateNavInput(0);
        if ($errors !== []) {
            $_SESSION['flash_errors'] = $errors;
            $this->redirect('/admin/navigation');

            return;
        }
        NavItem::create($this->normalizedPost());
        $_SESSION['flash_success'] = 'Menu item created.';
        $this->redirect('/admin/navigation');
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
            $this->redirect('/admin/navigation');

            return;
        }
        $errors = $this->validateNavInput($id);
        if ($errors !== []) {
            $_SESSION['flash_errors'] = $errors;
            $this->redirect('/admin/navigation');

            return;
        }
        NavItem::update($id, $this->normalizedPost());
        $_SESSION['flash_success'] = 'Menu item updated.';
        $this->redirect('/admin/navigation');
    }

    public function destroy(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';

            return;
        }
        NavItem::delete((int) Request::post('id', 0));
        $_SESSION['flash_success'] = 'Menu item removed.';
        $this->redirect('/admin/navigation');
    }

    public function reorder(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';

            return;
        }
        $order = Request::post('order', []);
        if (!is_array($order)) {
            $_SESSION['flash_error'] = 'Invalid order payload.';
            $this->redirect('/admin/navigation');

            return;
        }
        $ids = [];
        foreach ($order as $raw) {
            $ids[] = (int) $raw;
        }
        $ids = array_values(array_filter($ids, static fn (int $i): bool => $i > 0));
        if ($ids === []) {
            $_SESSION['flash_error'] = 'Nothing to reorder.';
            $this->redirect('/admin/navigation');

            return;
        }
        NavItem::reorderFromIdList($ids);
        $_SESSION['flash_success'] = 'Menu order saved.';
        $this->redirect('/admin/navigation');
    }

    public function reorderAjax(): void
    {
        $this->requireAuth();
        header('Content-Type: application/json; charset=utf-8');
        if (!Request::isJson()) {
            http_response_code(415);
            echo json_encode(['ok' => false, 'error' => 'Expected application/json']);

            return;
        }
        $data = Request::json();
        $token = is_array($data) ? (string) ($data['_token'] ?? '') : '';
        if (!Csrf::verify($token)) {
            http_response_code(419);
            echo json_encode(['ok' => false, 'error' => 'CSRF token mismatch']);

            return;
        }
        $tree = is_array($data) ? ($data['tree'] ?? null) : null;
        if (!is_array($tree)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Invalid tree payload']);

            return;
        }
        try {
            NavItem::applyOrderTree($tree);
            echo json_encode(['ok' => true]);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /** @return list<string> */
    private function validateNavInput(int $id): array
    {
        $errors = [];
        $label = NavItem::sanitizeLabel((string) Request::post('label', ''));
        if ($label === '') {
            $errors[] = 'Title is required.';
        }
        $url = NavItem::sanitizeUrl(trim((string) Request::post('url', '')));
        if ($url === '' || $url === '#') {
            $errors[] = 'Please enter a valid URL or path.';
        }
        $parentRaw = Request::post('parent_id', null);
        $parentId = null;
        if ($parentRaw !== null && $parentRaw !== '' && (string) $parentRaw !== '0') {
            $parentId = (int) $parentRaw;
            if ($parentId < 1) {
                $errors[] = 'Invalid parent.';
            }
        }
        if ($parentId !== null && $parentId === $id && $id > 0) {
            $errors[] = 'An item cannot be its own parent.';
        }
        if ($parentId !== null && $id > 0) {
            $desc = NavItem::descendantIds($id);
            if (in_array($parentId, $desc, true)) {
                $errors[] = 'Invalid parent (would create a loop).';
            }
        }
        if ($parentId !== null && $parentId > 0) {
            $p = NavItem::findById($parentId);
            if ($p === null) {
                $errors[] = 'Parent not found.';
            } else {
                $pp = $p['parent_id'] ?? null;
                if ($pp !== null && (int) $pp > 0) {
                    $errors[] = 'Nesting is limited to one sub-level: choose a top-level parent only.';
                }
            }
        }
        $isButton = (int) Request::post('is_button', 0) === 1;
        if ($isButton && $parentId !== null) {
            $errors[] = 'CTA buttons must be top-level (no parent).';
        }

        return $errors;
    }

    /** @return array<string, mixed> */
    private function normalizedPost(): array
    {
        $parentRaw = Request::post('parent_id', null);
        $parentId = null;
        if ($parentRaw !== null && $parentRaw !== '' && (string) $parentRaw !== '0') {
            $parentId = (int) $parentRaw;
            if ($parentId < 1) {
                $parentId = null;
            }
        }
        $isButton = (int) Request::post('is_button', 0) === 1;
        if ($isButton) {
            $parentId = null;
        }

        return [
            'parent_id' => $parentId,
            'label' => NavItem::sanitizeLabel((string) Request::post('label', '')),
            'url' => NavItem::sanitizeUrl(trim((string) Request::post('url', ''))),
            'slug' => NavItem::sanitizeSlug(trim((string) Request::post('slug', ''))),
            'icon' => NavItem::sanitizeIcon(trim((string) Request::post('icon', ''))),
            'order_index' => (int) Request::post('order_index', (int) Request::post('sort_order', 0)),
            'is_active' => (int) Request::post('is_active', 1),
            'open_new_tab' => (int) Request::post('open_new_tab', 0),
            'is_button' => $isButton ? 1 : 0,
        ];
    }
}
