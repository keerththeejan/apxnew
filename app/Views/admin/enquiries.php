<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Enquiries',
    'pageKey' => $pageKey ?? 'enquiries',
    'pageTitle' => $pageTitle ?? 'Customer Enquiries',
    'crumb' => $crumb ?? 'APX / Enquiries',
    'contentView' => __FILE__ . '.content',
    'q' => $q ?? '',
    'filter' => $filter ?? 'all',
    'messages' => $messages ?? [],
    'total' => $total ?? 0,
    'page' => $page ?? 1,
    'perPage' => $perPage ?? 15,
    'pageCount' => $pageCount ?? 1,
    'flashSuccess' => $flashSuccess ?? null,
    'flashError' => $flashError ?? null,
]);
