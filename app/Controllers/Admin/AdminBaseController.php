<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin;

abstract class AdminBaseController extends BaseController
{
    protected function requireAuth(): void
    {
        if (!isset($_SESSION['admin_id'])) {
            $this->redirect('/admin/login');
        }
    }

    /** @return array<string,mixed>|null */
    protected function currentAdmin(): ?array
    {
        $id = $_SESSION['admin_id'] ?? null;
        if ($id === null) {
            return null;
        }
        return Admin::findById((int) $id);
    }

    protected function isSuperAdmin(): bool
    {
        $a = $this->currentAdmin();
        if ($a === null) {
            return false;
        }
        $role = strtolower(trim((string) ($a['role'] ?? '')));
        // Only explicit "staff" is restricted; empty/missing role (legacy DB) = full access
        return $role !== 'staff';
    }

    protected function requireSuperAdmin(): void
    {
        $this->requireAuth();
        if (!$this->isSuperAdmin()) {
            $_SESSION['flash_error'] = 'You do not have permission for this action.';
            $this->redirect('/admin');
        }
    }
}
