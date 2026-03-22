<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Home banners',
    'pageKey' => $pageKey ?? 'banners',
    'pageTitle' => $pageTitle ?? 'Home banners',
    'crumb' => $crumb ?? 'APX / Home banners',
    'contentView' => __FILE__ . '.content',
    'items' => $items ?? [],
    'schemaReady' => $schemaReady ?? true,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
    'nextOrderIndex' => $nextOrderIndex ?? 0,
]);
