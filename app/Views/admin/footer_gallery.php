<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Footer gallery',
    'pageKey' => $pageKey ?? 'footer_gallery',
    'pageTitle' => $pageTitle ?? 'Footer gallery',
    'crumb' => $crumb ?? 'APX / Footer gallery',
    'contentView' => __FILE__ . '.content',
    'items' => $items ?? [],
    'schemaReady' => $schemaReady ?? true,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
    'nextSortOrder' => $nextSortOrder ?? 0,
]);
