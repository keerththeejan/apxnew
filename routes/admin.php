<?php

declare(strict_types=1);

use App\Controllers\Admin\ApplicationsController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\BlogPostsController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\DriversController;
use App\Controllers\Admin\EnquiriesController;
use App\Controllers\Admin\FinanceServicesController;
use App\Controllers\Admin\FlightsController;
use App\Controllers\Admin\FooterGalleryController;
use App\Controllers\Admin\HomeBannersController;
use App\Controllers\Admin\HotelsController;
use App\Controllers\Admin\InsurancePackagesController;
use App\Controllers\Admin\NavigationController;
use App\Controllers\Admin\PagesController;
use App\Controllers\Admin\QuoteRoutesController;
use App\Controllers\Admin\ServicesController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\UsersController;
use App\Controllers\Admin\VehicleBookingsController;
use App\Controllers\Admin\VehicleAnalyticsController;
use App\Controllers\Admin\VehicleMaintenanceController;
use App\Controllers\Admin\VehicleAvailabilityController;
use App\Controllers\Admin\VehiclePricingController;
use App\Controllers\Admin\VehiclesController;
use App\Controllers\Admin\VisasController;

$router->get('/admin/login.html', [AuthController::class, 'loginHtmlAlias']);
$router->get('/admin/login', [AuthController::class, 'showLogin']);
$router->post('/admin/login', [AuthController::class, 'login']);
$router->get('/admin/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/admin/forgot-password', [AuthController::class, 'forgotPassword']);
$router->get('/admin/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/admin/reset-password', [AuthController::class, 'resetPassword']);
$router->post('/admin/logout', [AuthController::class, 'logout']);
$router->get('/admin/logout', [AuthController::class, 'logout']);

$router->get('/admin/dashboard.html', [DashboardController::class, 'htmlAlias']);
$router->get('/admin', [DashboardController::class, 'index']);

$router->get('/admin/navigation.html', [NavigationController::class, 'htmlAlias']);
$router->get('/admin/navigation', [NavigationController::class, 'index']);
$router->post('/admin/navigation/create', [NavigationController::class, 'store']);
$router->post('/admin/navigation/update', [NavigationController::class, 'update']);
$router->post('/admin/navigation/delete', [NavigationController::class, 'destroy']);
$router->post('/admin/navigation/reorder', [NavigationController::class, 'reorder']);
$router->post('/admin/navigation/reorder-ajax', [NavigationController::class, 'reorderAjax']);

$router->get('/admin/banners.html', [HomeBannersController::class, 'htmlAlias']);
$router->get('/admin/banners', [HomeBannersController::class, 'index']);
$router->post('/admin/banners/create', [HomeBannersController::class, 'store']);
$router->post('/admin/banners/update', [HomeBannersController::class, 'update']);
$router->post('/admin/banners/delete', [HomeBannersController::class, 'destroy']);

$router->get('/admin/footer-gallery.html', [FooterGalleryController::class, 'htmlAlias']);
$router->get('/admin/footer-gallery', [FooterGalleryController::class, 'index']);
$router->post('/admin/footer-gallery/create', [FooterGalleryController::class, 'store']);
$router->post('/admin/footer-gallery/update', [FooterGalleryController::class, 'update']);
$router->post('/admin/footer-gallery/delete', [FooterGalleryController::class, 'destroy']);

$router->get('/admin/pages.html', [PagesController::class, 'htmlAlias']);
$router->get('/admin/pages', [PagesController::class, 'index']);
$router->post('/admin/pages/create', [PagesController::class, 'store']);
$router->post('/admin/pages/update', [PagesController::class, 'update']);
$router->post('/admin/pages/delete', [PagesController::class, 'destroy']);

$router->get('/admin/flights.html', [FlightsController::class, 'htmlAlias']);
$router->get('/admin/flights', [FlightsController::class, 'index']);
$router->post('/admin/flights/create', [FlightsController::class, 'store']);
$router->post('/admin/flights/update', [FlightsController::class, 'update']);
$router->post('/admin/flights/delete', [FlightsController::class, 'destroy']);

$router->get('/admin/hotels.html', [HotelsController::class, 'htmlAlias']);
$router->get('/admin/hotels', [HotelsController::class, 'index']);
$router->post('/admin/hotels/create', [HotelsController::class, 'store']);
$router->post('/admin/hotels/update', [HotelsController::class, 'update']);
$router->post('/admin/hotels/delete', [HotelsController::class, 'destroy']);

$router->get('/admin/visa.html', [VisasController::class, 'htmlAlias']);
$router->get('/admin/visa', [VisasController::class, 'index']);
$router->post('/admin/visa/create', [VisasController::class, 'store']);
$router->post('/admin/visa/update', [VisasController::class, 'update']);
$router->post('/admin/visa/delete', [VisasController::class, 'destroy']);

$router->get('/admin/services', [ServicesController::class, 'index']);
$router->post('/admin/services/create', [ServicesController::class, 'store']);
$router->post('/admin/services/update', [ServicesController::class, 'update']);
$router->post('/admin/services/delete', [ServicesController::class, 'destroy']);

$router->get('/admin/finance.html', [FinanceServicesController::class, 'htmlAlias']);
$router->get('/admin/finance', [FinanceServicesController::class, 'index']);
$router->post('/admin/finance/create', [FinanceServicesController::class, 'store']);
$router->post('/admin/finance/update', [FinanceServicesController::class, 'update']);
$router->post('/admin/finance/delete', [FinanceServicesController::class, 'destroy']);

$router->get('/admin/insurance.html', [InsurancePackagesController::class, 'htmlAlias']);
$router->get('/admin/insurance', [InsurancePackagesController::class, 'index']);
$router->post('/admin/insurance/create', [InsurancePackagesController::class, 'store']);
$router->post('/admin/insurance/update', [InsurancePackagesController::class, 'update']);
$router->post('/admin/insurance/delete', [InsurancePackagesController::class, 'destroy']);

$router->get('/admin/enquiries.html', [EnquiriesController::class, 'htmlAlias']);
$router->get('/admin/enquiries', [EnquiriesController::class, 'index']);
$router->get('/admin/vehicles.html', [VehiclesController::class, 'htmlAlias']);
$router->get('/admin/vehicles', [VehiclesController::class, 'index']);
$router->post('/admin/vehicles/create', [VehiclesController::class, 'store']);
$router->post('/admin/vehicles/update', [VehiclesController::class, 'update']);
$router->post('/admin/vehicles/delete', [VehiclesController::class, 'destroy']);
$router->get('/admin/drivers.html', [DriversController::class, 'htmlAlias']);
$router->get('/admin/drivers', [DriversController::class, 'index']);
$router->post('/admin/drivers/create', [DriversController::class, 'store']);
$router->post('/admin/drivers/update', [DriversController::class, 'update']);
$router->post('/admin/drivers/delete', [DriversController::class, 'destroy']);
$router->get('/admin/vehicle-bookings.html', [VehicleBookingsController::class, 'htmlAlias']);
$router->get('/admin/vehicle-bookings', [VehicleBookingsController::class, 'index']);
$router->post('/admin/vehicle-bookings/create', [VehicleBookingsController::class, 'store']);
$router->post('/admin/vehicle-bookings/update', [VehicleBookingsController::class, 'update']);
$router->post('/admin/vehicle-bookings/delete', [VehicleBookingsController::class, 'destroy']);
$router->post('/admin/vehicle-bookings/assign', [VehicleBookingsController::class, 'assign']);
$router->post('/admin/vehicle-bookings/status', [VehicleBookingsController::class, 'updateStatus']);
$router->get('/admin/vehicle-pricing.html', [VehiclePricingController::class, 'htmlAlias']);
$router->get('/admin/vehicle-pricing', [VehiclePricingController::class, 'index']);
$router->post('/admin/vehicle-pricing/create', [VehiclePricingController::class, 'store']);
$router->post('/admin/vehicle-pricing/update', [VehiclePricingController::class, 'update']);
$router->post('/admin/vehicle-pricing/delete', [VehiclePricingController::class, 'destroy']);
$router->get('/admin/vehicle-analytics.html', [VehicleAnalyticsController::class, 'htmlAlias']);
$router->get('/admin/vehicle-analytics', [VehicleAnalyticsController::class, 'index']);
$router->get('/admin/vehicle-maintenance.html', [VehicleMaintenanceController::class, 'htmlAlias']);
$router->get('/admin/vehicle-maintenance', [VehicleMaintenanceController::class, 'index']);
$router->post('/admin/vehicle-maintenance/create', [VehicleMaintenanceController::class, 'store']);
$router->post('/admin/vehicle-maintenance/delete', [VehicleMaintenanceController::class, 'destroy']);
$router->get('/admin/vehicle-availability.html', [VehicleAvailabilityController::class, 'htmlAlias']);
$router->get('/admin/vehicle-availability', [VehicleAvailabilityController::class, 'index']);
$router->post('/admin/vehicle-availability/create', [VehicleAvailabilityController::class, 'store']);
$router->post('/admin/vehicle-availability/delete', [VehicleAvailabilityController::class, 'destroy']);
$router->get('/admin/quotes.html', [QuoteRoutesController::class, 'htmlAlias']);
$router->get('/admin/quotes', [QuoteRoutesController::class, 'index']);
$router->post('/admin/quotes/create', [QuoteRoutesController::class, 'store']);
$router->post('/admin/quotes/update', [QuoteRoutesController::class, 'update']);
$router->post('/admin/quotes/delete', [QuoteRoutesController::class, 'destroy']);
$router->post('/admin/enquiries/read', [EnquiriesController::class, 'markRead']);
$router->post('/admin/enquiries/unread', [EnquiriesController::class, 'markUnread']);
$router->post('/admin/enquiries/delete', [EnquiriesController::class, 'destroy']);

$router->get('/admin/news.html', [BlogPostsController::class, 'newsAlias']);
$router->get('/admin/news', [BlogPostsController::class, 'newsAlias']);
$router->get('/admin/blog', [BlogPostsController::class, 'index']);
$router->get('/admin/blog/new', [BlogPostsController::class, 'create']);
$router->post('/admin/blog/store', [BlogPostsController::class, 'store']);
$router->get('/admin/blog/edit/{id}', [BlogPostsController::class, 'edit']);
$router->post('/admin/blog/update', [BlogPostsController::class, 'update']);
$router->post('/admin/blog/delete', [BlogPostsController::class, 'destroy']);

$router->get('/admin/applications.html', [ApplicationsController::class, 'htmlAlias']);
$router->get('/admin/applications', [ApplicationsController::class, 'index']);
$router->post('/admin/applications/status', [ApplicationsController::class, 'updateStatus']);
$router->post('/admin/applications/bulk-whatsapp', [ApplicationsController::class, 'bulkWhatsapp']);
$router->get('/admin/applications/export', [ApplicationsController::class, 'exportCsv']);

$router->get('/admin/users.html', [UsersController::class, 'htmlAlias']);
$router->get('/admin/users', [UsersController::class, 'index']);
$router->post('/admin/users/toggle', [UsersController::class, 'toggleActive']);
$router->post('/admin/users/role', [UsersController::class, 'updateRole']);
$router->get('/admin/users/export', [UsersController::class, 'exportCsv']);

$router->get('/admin/settings.html', [SettingsController::class, 'htmlAlias']);
$router->get('/admin/settings', [SettingsController::class, 'index']);
$router->post('/admin/settings/save', [SettingsController::class, 'save']);
$router->post('/admin/settings/email/test', [SettingsController::class, 'sendTestEmail']);
$router->get('/admin/settings/whatsapp', [SettingsController::class, 'whatsapp']);
$router->post('/admin/settings/whatsapp/save', [SettingsController::class, 'saveWhatsapp']);
$router->post('/admin/settings/whatsapp/send', [SettingsController::class, 'sendWhatsapp']);
