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
    public function htmlAlias(): void
    {
        $this->redirect('/admin');
    }

    public function index(): void
    {
        $this->requireAuth();

        view('admin.dashboard', [
            'totalApplications' => Application::countAll(),
            'totalServices' => Service::countAll(),
            'totalPosts' => BlogPost::countAll(),
            'totalUsers' => User::countAll(),
            'notificationCount' => AdminNotification::unreadCount(),
            'recentApplications' => Application::latest(10),
            'recentContactMessages' => ContactMessage::latest(10),
        ]);
    }
}
