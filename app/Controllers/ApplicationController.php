<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Application;
use App\Models\ApplicationFormField;

final class ApplicationController extends BaseController
{
    public function store(): void
    {
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $fields = ApplicationFormField::activeOrDefault();

        $input = [];
        foreach ($fields as $f) {
            $name = (string) ($f['field_name'] ?? '');
            if ($name === '') {
                continue;
            }
            $input[$name] = (string) Request::post($name, '');
        }

        $rules = [];
        foreach ($fields as $f) {
            $name = (string) ($f['field_name'] ?? '');
            if ($name === '') {
                continue;
            }
            $checks = [];
            if ((int) ($f['is_required'] ?? 0) === 1) {
                $checks[] = 'required';
            }
            $type = (string) ($f['field_type'] ?? 'text');
            if ($type === 'email') {
                $checks[] = 'email';
            }
            $max = $type === 'textarea' ? 5000 : 255;
            if ($type === 'tel') {
                $max = 40;
            }
            $checks[] = ['max', $max];
            $rules[$name] = $checks;
        }

        $errors = Validator::errors($rules, $input);

        foreach ($fields as $f) {
            $name = (string) ($f['field_name'] ?? '');
            $type = (string) ($f['field_type'] ?? 'text');
            if ($type !== 'select' || $name === '') {
                continue;
            }
            $raw = (string) ($input[$name] ?? '');
            if ($raw === '') {
                continue;
            }
            $opts = json_decode((string) ($f['options_json'] ?? '[]'), true);
            if (!is_array($opts)) {
                $opts = [];
            }
            $allowed = array_map('strval', $opts);
            if ($allowed !== [] && !in_array($raw, $allowed, true)) {
                $errors[$name][] = 'Invalid selection.';
            }
        }

        $payload = json_encode($input, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $hash = hash('sha256', $payload);
        if (isset($_SESSION['last_application_hash']) && $_SESSION['last_application_hash'] === $hash) {
            $_SESSION['flash_error'] = 'This application was already submitted.';
            $_SESSION['flash_old'] = $input;
            $this->redirect('/#apply');
            return;
        }

        if (isset($_SESSION['application_cooldown_until']) && time() < (int) $_SESSION['application_cooldown_until']) {
            $_SESSION['flash_error'] = 'Please wait a moment before submitting again.';
            $_SESSION['flash_old'] = $input;
            $this->redirect('/#apply');
            return;
        }

        if ($errors !== []) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['flash_old'] = $input;
            $this->redirect('/#apply');
        }

        $name = (string) ($input['name'] ?? $input['full_name'] ?? '');
        $phone = (string) ($input['phone'] ?? '');
        $email = (string) ($input['email'] ?? '');
        $serviceType = (string) ($input['service_type'] ?? $input['service'] ?? '');
        $message = (string) ($input['message'] ?? $input['notes'] ?? '');

        Application::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'service_type' => $serviceType,
            'message' => $message,
            'status' => 'pending',
            'form_data_json' => $payload,
        ]);

        $_SESSION['last_application_hash'] = $hash;
        $_SESSION['application_cooldown_until'] = time() + 45;

        $_SESSION['flash_success'] = 'Thank you. We will contact you shortly.';
        $this->redirect('/#apply');
    }
}
