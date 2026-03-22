<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Blog',
    'pageKey' => $pageKey ?? 'blog',
    'pageTitle' => $pageTitle ?? 'Blog',
    'crumb' => $crumb ?? 'APX / Blog',
    'contentView' => __FILE__ . '.content',
    'post' => $post ?? null,
    'mode' => $mode ?? 'create',
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);
