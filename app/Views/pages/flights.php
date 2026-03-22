<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'Flights',
    'metaDescription' => $metaDescription ?? '',
    'page' => $page ?? null,
    'deals' => $deals ?? [],
    'destinations' => $destinations ?? [],
    'contentView' => __FILE__ . '.content',
]);
