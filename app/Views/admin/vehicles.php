<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Vehicles',
    'pageKey' => $pageKey ?? 'vehicles',
    'pageTitle' => $pageTitle ?? 'Vehicles',
    'crumb' => $crumb ?? 'APX / Vehicles',
    'contentView' => __FILE__ . '.content',
    'items' => $items ?? [],
    'q' => $q ?? '',
    'type' => $type ?? '',
    'page' => $page ?? 1,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
    'schemaReady' => $schemaReady ?? true,
]);
