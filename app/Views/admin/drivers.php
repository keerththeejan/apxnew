<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Drivers',
    'pageKey' => $pageKey ?? 'drivers',
    'pageTitle' => $pageTitle ?? 'Drivers',
    'crumb' => $crumb ?? 'APX / Drivers',
    'contentView' => __FILE__ . '.content',
    'items' => $items ?? [],
    'q' => $q ?? '',
    'status' => $status ?? '',
    'page' => $page ?? 1,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
    'schemaReady' => $schemaReady ?? true,
]);
