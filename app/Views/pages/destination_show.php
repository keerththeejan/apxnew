<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? (string) ($destination['name'] ?? 'Destination'),
    'metaDescription' => $metaDescription ?? '',
    'destination' => $destination ?? [],
    'contentView' => __FILE__ . '.content',
]);
