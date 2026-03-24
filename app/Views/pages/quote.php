<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'Get a quote',
    'metaDescription' => $metaDescription ?? '',
    'page' => $page ?? [],
    'contentView' => __FILE__ . '.content',
]);
