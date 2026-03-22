<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => 'APX Admin - Dashboard',
    'pageKey' => 'dashboard',
    'pageTitle' => 'Dashboard',
    'crumb' => 'APX / Dashboard',
    'contentView' => __FILE__ . '.content',
    'totalApplications' => $totalApplications ?? 0,
    'totalServices' => $totalServices ?? 0,
    'totalPosts' => $totalPosts ?? 0,
    'totalUsers' => $totalUsers ?? 0,
    'notificationCount' => $notificationCount ?? 0,
    'recentApplications' => $recentApplications ?? [],
    'recentContactMessages' => $recentContactMessages ?? [],
]);
