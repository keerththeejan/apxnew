<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Vehicle Maintenance',
    'pageKey' => $pageKey ?? 'vehicle_maintenance',
    'pageTitle' => $pageTitle ?? 'Vehicle Maintenance',
    'crumb' => $crumb ?? 'APX / Vehicle Maintenance',
    'contentView' => __FILE__ . '.content',
    'items' => $items ?? [],
    'vehicles' => $vehicles ?? [],
    'page' => $page ?? 1,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
    'schemaReady' => $schemaReady ?? true,
]);
