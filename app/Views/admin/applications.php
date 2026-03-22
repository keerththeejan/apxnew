<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Applications',
    'pageKey' => $pageKey ?? 'applications',
    'pageTitle' => $pageTitle ?? 'Applications',
    'crumb' => $crumb ?? 'APX / Applications',
    'contentView' => __FILE__ . '.content',
    'q' => $q ?? '',
    'status' => $status ?? 'all',
    'applications' => $applications ?? [],
    'total' => $total ?? 0,
    'page' => $page ?? 1,
    'perPage' => $perPage ?? 15,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);
