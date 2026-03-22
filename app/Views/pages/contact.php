<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'Contact',
    'metaDescription' => $metaDescription ?? '',
    'page' => $page ?? [],
    'contentView' => __FILE__ . '.content',
]);
