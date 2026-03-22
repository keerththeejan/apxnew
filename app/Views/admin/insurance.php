<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Insurance Plans',
    'pageKey' => $pageKey ?? 'insurance',
    'pageTitle' => $pageTitle ?? 'Insurance Services',
    'crumb' => $crumb ?? 'APX / Insurance',
    'contentView' => __FILE__ . '.content',
    'q' => $q ?? '',
    'items' => $items ?? [],
    'total' => $total ?? 0,
    'page' => $page ?? 1,
    'perPage' => $perPage ?? 12,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);
