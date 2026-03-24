<?php

declare(strict_types=1);

view('layouts.admin', [
    'title' => $title ?? 'APX Admin - Vehicle Analytics',
    'pageKey' => $pageKey ?? 'vehicle_analytics',
    'pageTitle' => $pageTitle ?? 'Vehicle Analytics',
    'crumb' => $crumb ?? 'APX / Vehicle Analytics',
    'contentView' => __FILE__ . '.content',
    'totalBookings' => $totalBookings ?? 0,
    'activeTrips' => $activeTrips ?? 0,
    'dailyRevenue' => $dailyRevenue ?? 0,
    'monthlyRevenue' => $monthlyRevenue ?? 0,
    'statusCounts' => $statusCounts ?? [],
    'driverPerformance' => $driverPerformance ?? [],
    'vehicleUtilization' => $vehicleUtilization ?? [],
]);
