<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Settings',
    'pageKey' => $pageKey ?? 'settings',
    'pageTitle' => $pageTitle ?? 'Settings',
    'crumb' => $crumb ?? 'Settings',
    'contentView' => __FILE__ . '.content',
    'settings' => $settings ?? [],
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);
