<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'Hotels',
    'metaDescription' => $metaDescription ?? '',
    'page' => $page ?? null,
    'hotels' => $hotels ?? [],
    'destinations' => $destinations ?? [],
    'contentView' => __FILE__ . '.content',
]);
