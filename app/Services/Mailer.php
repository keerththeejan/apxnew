<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Minimal mail helper: uses PHP mail() when available; always appends to a log file for debugging.
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $body, ?string $from = null): bool
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $logFile = $dir . '/mail.log';
        $line = date('c') . " | To: {$to} | " . $subject . "\n" . $body . "\n---\n";
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

        $from = $from ?? (string) (env('MAIL_FROM', 'noreply@localhost'));
        $headers = 'From: ' . $from . "\r\nContent-Type: text/plain; charset=UTF-8";

        return @mail($to, $subject, $body, $headers);
    }
}
