<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => 'Page not found',
    'metaDescription' => '',
    'contentView' => __FILE__ . '.content',
    'path' => $path ?? '/',
]);
