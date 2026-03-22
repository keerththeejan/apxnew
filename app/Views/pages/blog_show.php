<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? (string) ($post['title'] ?? 'Blog'),
    'metaDescription' => $metaDescription ?? '',
    'post' => $post ?? [],
    'contentView' => __FILE__ . '.content',
]);
