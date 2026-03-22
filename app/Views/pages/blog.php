<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'Blog',
    'metaDescription' => $metaDescription ?? '',
    'page' => $page ?? null,
    'posts' => $posts ?? [],
    'contentView' => __FILE__ . '.content',
]);
