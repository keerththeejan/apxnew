<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Visa Services',
    'pageKey' => $pageKey ?? 'visa',
    'pageTitle' => $pageTitle ?? 'Visa Services',
    'crumb' => $crumb ?? 'APX / Visa Services',
    'contentView' => __FILE__ . '.content',
    'q' => $q ?? '',
    'visas' => $visas ?? [],
    'total' => $total ?? 0,
    'page' => $page ?? 1,
    'perPage' => $perPage ?? 12,
    'pageCount' => $pageCount ?? 1,
    'app_q' => $app_q ?? '',
    'app_status' => $app_status ?? 'all',
    'applications' => $applications ?? [],
    'appTotal' => $appTotal ?? 0,
    'appPage' => $appPage ?? 1,
    'appPageCount' => $appPageCount ?? 1,
    'destinations' => $destinations ?? [],
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);
