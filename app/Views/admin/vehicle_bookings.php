<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Vehicle Bookings',
    'pageKey' => $pageKey ?? 'vehicle_bookings',
    'pageTitle' => $pageTitle ?? 'Vehicle Bookings',
    'crumb' => $crumb ?? 'APX / Vehicle Bookings',
    'contentView' => __FILE__ . '.content',
    'filters' => $filters ?? [],
    'items' => $items ?? [],
    'vehicles' => $vehicles ?? [],
    'drivers' => $drivers ?? [],
    'statusCounts' => $statusCounts ?? [],
    'page' => $page ?? 1,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
    'schemaReady' => $schemaReady ?? true,
]);
