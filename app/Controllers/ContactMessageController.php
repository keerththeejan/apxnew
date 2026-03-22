<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Validator;
use App\Models\ContactMessage;

final class ContactMessageController extends BaseController
{
    public function store(): void
    {
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $input = [
            'name' => (string) Request::post('name', ''),
            'phone' => (string) Request::post('phone', ''),
            'email' => (string) Request::post('email', ''),
            'subject' => (string) Request::post('subject', ''),
            'message' => (string) Request::post('message', ''),
        ];

        $errors = Validator::errors([
            'name' => ['required', ['max', 120]],
            'phone' => [['max', 40]],
            'email' => ['email', ['max', 190]],
            'subject' => [['max', 180]],
            'message' => ['required', ['max', 2000]],
        ], $input);

        if ($errors !== []) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['flash_old'] = $input;
            $this->redirect('/contact');
        }

        ContactMessage::create($input);
        $_SESSION['flash_success'] = 'Thank you. We will get back to you shortly.';
        $this->redirect('/contact');
    }
}
