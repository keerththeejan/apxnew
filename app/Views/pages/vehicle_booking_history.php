<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'My Vehicle Bookings',
    'metaDescription' => $metaDescription ?? '',
    'contentView' => __FILE__ . '.content',
    'phone' => $phone ?? '',
    'bookings' => $bookings ?? [],
]);
