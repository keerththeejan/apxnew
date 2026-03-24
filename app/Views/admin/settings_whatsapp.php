<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - WhatsApp Settings',
    'pageKey' => $pageKey ?? 'settings',
    'pageTitle' => $pageTitle ?? 'WhatsApp Settings',
    'crumb' => $crumb ?? 'Settings / WhatsApp',
    'contentView' => __FILE__ . '.content',
    'settings' => $settings ?? [],
    'logs' => $logs ?? [],
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);

