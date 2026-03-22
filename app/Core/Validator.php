<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function required($value): bool
    {
        if ($value === null) {
            return false;
        }
        if (is_string($value)) {
            return trim($value) !== '';
        }
        if (is_array($value)) {
            return count($value) > 0;
        }
        return true;
    }

    public static function email($value): bool
    {
        if (!is_string($value) || trim($value) === '') {
            return true;
        }
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function maxLen($value, int $max): bool
    {
        if (!is_string($value)) {
            return true;
        }
        return mb_strlen($value) <= $max;
    }

    public static function errors(array $rules, array $input): array
    {
        $errors = [];

        foreach ($rules as $field => $checks) {
            $value = $input[$field] ?? null;

            foreach ($checks as $check) {
                if ($check === 'required' && !self::required($value)) {
                    $errors[$field][] = 'This field is required.';
                }
                if ($check === 'email' && !self::email($value)) {
                    $errors[$field][] = 'Please enter a valid email.';
                }
                if (is_array($check) && ($check[0] ?? null) === 'max') {
                    $max = (int) ($check[1] ?? 0);
                    if (!self::maxLen((string) $value, $max)) {
                        $errors[$field][] = 'Too long.';
                    }
                }
            }
        }

        return $errors;
    }
}
