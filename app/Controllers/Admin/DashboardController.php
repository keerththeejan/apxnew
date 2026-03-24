<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AdminNotification;
use App\Models\Application;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Service;
use App\Models\User;

final class DashboardController extends AdminBaseController
{
    /** @template T @param callable():T $fn @param T $fallback @return T */
    private function safe(callable $fn, $fallback)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            error_log('[admin.dashboard] ' . $e->getMessage());
            return $fallback;
        }
    }

    public function htmlAlias(): void
    {
        $this->redirect('/admin');
    }

    public function index(): void
    {
        $this->requireAuth();

        view('admin.dashboard', [
            'totalApplications' => $this->safe(static fn(): int => Application::countAll(), 0),
            'totalServices' => $this->safe(static fn(): int => Service::countAll(), 0),
            'totalPosts' => $this->safe(static fn(): int => BlogPost::countAll(), 0),
            'totalUsers' => $this->safe(static fn(): int => User::countAll(), 0),
            'notificationCount' => $this->safe(static fn(): int => AdminNotification::unreadCount(), 0),
            'recentApplications' => $this->safe(static fn(): array => Application::latest(10), []),
            'recentContactMessages' => $this->safe(static fn(): array => ContactMessage::latest(10), []),
        ]);
    }
}
