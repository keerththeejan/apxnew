<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'Live Trip Tracking',
    'metaDescription' => $metaDescription ?? '',
    'contentView' => __FILE__ . '.content',
    'bookingRef' => $bookingRef ?? '',
]);
