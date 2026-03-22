<?php

declare(strict_types=1);

$flashErrors = $flashErrors ?? [];
$flashOld = $flashOld ?? [];
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;

view('layouts.admin', [
    'title' => 'APX Admin - Flights',
    'pageKey' => 'flights',
    'pageTitle' => 'Flights',
    'crumb' => 'APX / Flights',
    'contentView' => __FILE__ . '.content',

    'q' => $q ?? '',
    'flights' => $flights ?? [],
    'total' => $total ?? 0,
    'page' => $page ?? 1,
    'perPage' => $perPage ?? 10,
    'pageCount' => $pageCount ?? 1,
    'sort' => $sort ?? 'updated_at',
    'dir' => $dir ?? 'DESC',

    'flashErrors' => $flashErrors,
    'flashOld' => $flashOld,
    'flashSuccess' => $flashSuccess,
    'flashError' => $flashError,
]);
