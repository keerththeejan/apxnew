<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Vehicle Pricing',
    'pageKey' => $pageKey ?? 'vehicle_pricing',
    'pageTitle' => $pageTitle ?? 'Vehicle Pricing',
    'crumb' => $crumb ?? 'APX / Vehicle Pricing',
    'contentView' => __FILE__ . '.content',
    'items' => $items ?? [],
    'vehicleType' => $vehicleType ?? '',
    'page' => $page ?? 1,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
    'schemaReady' => $schemaReady ?? true,
]);
