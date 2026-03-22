<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Services',
    'pageKey' => $pageKey ?? 'services',
    'pageTitle' => $pageTitle ?? 'Services',
    'crumb' => $crumb ?? 'APX / Services',
    'contentView' => __FILE__ . '.content',
    'q' => $q ?? '',
    'services' => $services ?? [],
    'total' => $total ?? 0,
    'page' => $page ?? 1,
    'perPage' => $perPage ?? 12,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);
