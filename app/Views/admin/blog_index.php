<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Blog',
    'pageKey' => $pageKey ?? 'blog',
    'pageTitle' => $pageTitle ?? 'Blog',
    'crumb' => $crumb ?? 'APX / Blog',
    'contentView' => __FILE__ . '.content',
    'q' => $q ?? '',
    'posts' => $posts ?? [],
    'total' => $total ?? 0,
    'page' => $page ?? 1,
    'perPage' => $perPage ?? 12,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);
