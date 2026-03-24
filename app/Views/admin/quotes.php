<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Quote management',
    'pageKey' => $pageKey ?? 'quotes',
    'pageTitle' => $pageTitle ?? 'Quote management',
    'crumb' => $crumb ?? 'APX / Quotes',
    'contentView' => __FILE__ . '.content',
    'items' => $items ?? [],
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
    'schemaReady' => $schemaReady ?? true,
]);
