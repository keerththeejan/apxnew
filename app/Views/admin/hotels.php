<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Hotels',
    'pageKey' => $pageKey ?? 'hotels',
    'pageTitle' => $pageTitle ?? 'Hotel Bookings',
    'crumb' => $crumb ?? 'APX / Hotels',
    'contentView' => __FILE__ . '.content',
    'q' => $q ?? '',
    'items' => $items ?? [],
    'total' => $total ?? 0,
    'page' => $page ?? 1,
    'perPage' => $perPage ?? 12,
    'pageCount' => $pageCount ?? 1,
    'destinations' => $destinations ?? [],
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);
