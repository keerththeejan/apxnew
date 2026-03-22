<?php

declare(strict_types=1);

view_public('layouts.app', [
    'title' => $title ?? 'Home',
    'bodyClass' => 'page-home',
    'metaDescription' => $metaDescription ?? '',
    'featuredDestinations' => $featuredDestinations ?? [],
    'testimonials' => $testimonials ?? [],
    'posts' => $posts ?? [],
    'services' => $services ?? [],
    'ctaMid' => $ctaMid ?? null,
    'ctaNews' => $ctaNews ?? null,
    'formFields' => $formFields ?? [],
    'contentView' => __FILE__ . '.content',
]);
