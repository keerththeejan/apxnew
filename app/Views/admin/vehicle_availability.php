<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Vehicle Availability',
    'pageKey' => $pageKey ?? 'vehicle_availability',
    'pageTitle' => $pageTitle ?? 'Vehicle Availability',
    'crumb' => $crumb ?? 'APX / Vehicle Availability',
    'contentView' => __FILE__ . '.content',
    'items' => $items ?? [],
    'vehicles' => $vehicles ?? [],
    'page' => $page ?? 1,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
    'schemaReady' => $schemaReady ?? true,
]);
