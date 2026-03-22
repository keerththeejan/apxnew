<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'Visa Services',
    'metaDescription' => $metaDescription ?? '',
    'page' => $page ?? null,
    'visas' => $visas ?? [],
    'destinations' => $destinations ?? [],
    'contentView' => __FILE__ . '.content',
]);
