<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'Destinations',
    'metaDescription' => $metaDescription ?? '',
    'page' => $page ?? null,
    'q' => $q ?? '',
    'destinations' => $destinations ?? [],
    'contentView' => __FILE__ . '.content',
]);
