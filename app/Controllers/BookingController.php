<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Booking;
use App\Services\WhatsAppService;

final class BookingController extends BaseController
{
    public function visa(): void
    {
        $this->store('visa');
    }

    public function flight(): void
    {
        $this->store('flight');
    }

    public function hotel(): void
    {
        $this->store('hotel');
    }

    public function insurance(): void
    {
        $this->store('insurance');
    }

    private function store(string $type): void
    {
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $input = [
            'type' => $type,
            'full_name' => (string) Request::post('full_name', ''),
            'phone' => (string) Request::post('phone', ''),
            'email' => (string) Request::post('email', ''),
            'destination' => (string) Request::post('destination', ''),
            'travel_date' => (string) Request::post('travel_date', ''),
            'notes' => (string) Request::post('notes', ''),
        ];

        $errors = Validator::errors([
            'full_name' => ['required', ['max', 120]],
            'phone' => ['required', ['max', 40]],
            'email' => ['email', ['max', 190]],
            'destination' => [['max', 120]],
            'travel_date' => [['max', 20]],
            'notes' => [['max', 2000]],
        ], $input);

        if ($errors !== []) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['flash_old'] = $input;
            $this->redirect('/');
        }

        $code = Booking::create($input);
        try {
            $msg = WhatsAppService::renderTemplate('whatsapp_tpl_new_order', [
                'name' => (string) $input['full_name'],
                'code' => $code,
                'status' => 'new',
                'service' => strtoupper((string) $type),
            ]);
            WhatsAppService::sendText((string) $input['phone'], $msg, 'booking.created', null);
        } catch (\Throwable) {
        }
        $this->redirect('/booking/confirmation/' . $code);
    }

    public function confirmation(string $code): void
    {
        $booking = Booking::findByCode($code);
        if ($booking === null) {
            http_response_code(404);
            view('errors.404', ['path' => '/booking/confirmation/' . $code]);
            return;
        }

        view('pages.booking_confirmation', [
            'booking' => $booking,
        ]);
    }
}
