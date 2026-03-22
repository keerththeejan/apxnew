<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\InquiryController;
use App\Controllers\BookingController;
use App\Controllers\SeoController;
use App\Controllers\ApplicationController;
use App\Controllers\ContactMessageController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/visa.html', [PageController::class, 'visaHtmlRedirect']);
$router->get('/settings.html', [PageController::class, 'settingsHtmlRedirect']);
$router->get('/sitemap.xml', [SeoController::class, 'sitemap']);
$router->get('/about', [PageController::class, 'about']);
$router->get('/contact', [PageController::class, 'contact']);

$router->get('/visas', [PageController::class, 'visas']);
$router->get('/flights', [PageController::class, 'flights']);
$router->get('/hotels', [PageController::class, 'hotels']);
$router->get('/insurance', [PageController::class, 'insurance']);

$router->get('/destinations', [PageController::class, 'destinations']);
$router->get('/destinations/{slug}', [PageController::class, 'destinationShow']);

$router->get('/blog', [PageController::class, 'blog']);
$router->get('/blog/{slug}', [PageController::class, 'blogShow']);

$router->post('/inquiries', [InquiryController::class, 'store']);

$router->post('/applications', [ApplicationController::class, 'store']);
$router->post('/contact-messages', [ContactMessageController::class, 'store']);

$router->post('/bookings/visa', [BookingController::class, 'visa']);
$router->post('/bookings/flight', [BookingController::class, 'flight']);
$router->post('/bookings/hotel', [BookingController::class, 'hotel']);
$router->post('/bookings/insurance', [BookingController::class, 'insurance']);

$router->get('/booking/confirmation/{code}', [BookingController::class, 'confirmation']);
