<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Setting;
use App\Models\VehicleBooking;

final class VehicleAnalyticsController extends AdminBaseController
{
    public function htmlAlias(): void
    {
        $this->redirect('/admin/vehicle-analytics');
    }

    public function index(): void
    {
        $this->requireAuth();

        $today = date('Y-m-d');
        $month = date('Y-m');
        $statusCounts = VehicleBooking::countsByStatus();
        $activeTrips = (int) (($statusCounts['assigned'] ?? 0) + ($statusCounts['on_trip'] ?? 0));

        $s = Setting::allKeyed();
        $siteName = (string) ($s['site_name'] ?? 'APX');
        view('admin.vehicle_analytics', [
            'title' => 'APX Admin - Vehicle Analytics',
            'pageKey' => 'vehicle_analytics',
            'pageTitle' => 'Vehicle Analytics',
            'crumb' => $siteName . ' / Vehicle Analytics',
            'totalBookings' => VehicleBooking::countAll(),
            'activeTrips' => $activeTrips,
            'dailyRevenue' => VehicleBooking::revenueForDate($today),
            'monthlyRevenue' => VehicleBooking::revenueForMonth($month),
            'statusCounts' => $statusCounts,
            'driverPerformance' => VehicleBooking::driverPerformance(),
            'vehicleUtilization' => VehicleBooking::vehicleUtilization(),
        ]);
    }
}
