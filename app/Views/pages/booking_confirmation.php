<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => 'Booking Confirmation',
    'metaDescription' => '',
    'booking' => $booking ?? [],
    'contentView' => __FILE__ . '.content',
]);
