<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Menu management',
    'pageKey' => $pageKey ?? 'navigation',
    'pageTitle' => $pageTitle ?? 'Menu management',
    'crumb' => $crumb ?? 'APX / Menu management',
    'contentView' => __FILE__ . '.content',
    'items' => $items ?? [],
    'menuTree' => $menuTree ?? [],
    'itemsById' => $itemsById ?? [],
    'flashErrors' => $flashErrors ?? [],
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);
