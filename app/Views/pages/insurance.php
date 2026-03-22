<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'Insurance',
    'metaDescription' => $metaDescription ?? '',
    'page' => $page ?? null,
    'packages' => $packages ?? [],
    'contentView' => __FILE__ . '.content',
]);
