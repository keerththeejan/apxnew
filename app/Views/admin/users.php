<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Users',
    'pageKey' => $pageKey ?? 'users',
    'pageTitle' => $pageTitle ?? 'Users',
    'crumb' => $crumb ?? 'APX / Users',
    'contentView' => __FILE__ . '.content',
    'q' => $q ?? '',
    'users' => $users ?? [],
    'total' => $total ?? 0,
    'page' => $page ?? 1,
    'perPage' => $perPage ?? 15,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);
