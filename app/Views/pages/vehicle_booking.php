<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'Vehicle Booking',
    'metaDescription' => $metaDescription ?? '',
    'contentView' => __FILE__ . '.content',
    'mapApiKey' => $mapApiKey ?? '',
    'vehicleTypes' => $vehicleTypes ?? [],
]);
