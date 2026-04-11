<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Sends mail via SMTP (SSL, e.g. port 465) when host is set (admin settings or .env); otherwise mail().
 * Appends every attempt to storage/logs/mail.log.
 */
final class Mailer
{
    /** DB `settings` value wins when non-empty; else env. */
    private static function conf(string $settingKey, string $envKey, string $default = ''): string
    {
        $db = trim(SiteConfig::get($settingKey, ''));
        if ($db !== '') {
            return $db;
        }
        $e = env($envKey, $default);

        return trim((string) ($e !== null && $e !== '' ? $e : $default));
    }

    private static function mailFromAddress(): string
    {
        $addr = self::conf('mail_from_address', 'MAIL_FROM_ADDRESS', '');
        if ($addr === '') {
            $addr = trim((string) (env('MAIL_FROM', '') ?? ''));
        }

        return $addr;
    }

    public static function send(string $to, string $subject, string $body, ?string $from = null): bool
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $logFile = $dir . '/mail.log';
        $from = $from ?? self::defaultFromHeader();

        $line = date('c') . " | To: {$to} | " . $subject . "\n" . $body . "\n";

        $host = self::conf('mail_host', 'MAIL_HOST', '');
        $ok = $host !== ''
            ? self::sendViaSmtp($to, $subject, $body, $from, $logFile)
            : @mail($to, $subject, $body, self::mailHeaders($from));

        @file_put_contents(
            $logFile,
            $line . ($ok ? "[ok]\n" : "[fail]\n") . "---\n",
            FILE_APPEND | LOCK_EX
        );

        return $ok;
    }

    private static function defaultFromHeader(): string
    {
        $addr = self::mailFromAddress();
        if ($addr === '') {
            $addr = 'noreply@localhost';
        }
        $name = self::conf('mail_from_name', 'MAIL_FROM_NAME', '');
        if ($name === '') {
            return $addr;
        }
        return self::encodeHeaderName($name) . ' <' . $addr . '>';
    }

    private static function encodeHeaderName(string $name): string
    {
        if (!preg_match('/[^\x20-\x7E]/', $name)) {
            return '"' . addcslashes($name, '"\\') . '"';
        }
        if (function_exists('mb_encode_mimeheader')) {
            return mb_encode_mimeheader($name, 'UTF-8', 'B', "\r\n");
        }
        return '=?UTF-8?B?' . base64_encode($name) . '?=';
    }

    private static function mailHeaders(string $from): string
    {
        return 'From: ' . $from . "\r\nContent-Type: text/plain; charset=UTF-8";
    }

    private static function sendViaSmtp(
        string $to,
        string $subject,
        string $body,
        string $from,
        string $logFile
    ): bool {
        $host = self::conf('mail_host', 'MAIL_HOST', '');
        $portRaw = self::conf('mail_port', 'MAIL_PORT', '465');
        $port = (int) ($portRaw !== '' ? $portRaw : '465');
        if ($port < 1 || $port > 65535) {
            $port = 465;
        }
        $user = self::conf('mail_username', 'MAIL_USERNAME', '');
        $pass = self::conf('mail_password', 'MAIL_PASSWORD', '');
        if ($host === '' || $user === '' || $pass === '') {
            self::smtpLog($logFile, 'SMTP skipped: missing host, username, or password (settings or .env)');
            return false;
        }

        $verifyRaw = strtolower(trim(self::conf('mail_ssl_verify', 'MAIL_SSL_VERIFY', '1')));
        $verify = !in_array($verifyRaw, ['0', 'false', 'no', 'off'], true);

        $ctx = stream_context_create([
            'ssl' => [
                'verify_peer' => $verify,
                'verify_peer_name' => $verify,
                'allow_self_signed' => !$verify,
            ],
        ]);

        $remote = 'ssl://' . $host . ':' . $port;
        $fp = @stream_socket_client($remote, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $ctx);
        if ($fp === false) {
            self::smtpLog($logFile, "SMTP connect failed: [{$errno}] {$errstr}");
            return false;
        }

        stream_set_timeout($fp, 60);

        if (!self::smtpExpect($fp, [220])) {
            self::smtpLog($logFile, 'SMTP bad greeting: ' . self::smtpLastResponse());
            fclose($fp);
            return false;
        }

        fwrite($fp, 'EHLO ' . self::smtpEhloHost() . "\r\n");
        if (!self::smtpExpect($fp, [250])) {
            self::smtpLog($logFile, 'SMTP EHLO failed: ' . self::smtpLastResponse());
            fclose($fp);
            return false;
        }

        fwrite($fp, "AUTH LOGIN\r\n");
        if (!self::smtpExpect($fp, [334])) {
            self::smtpLog($logFile, 'SMTP AUTH LOGIN not accepted: ' . self::smtpLastResponse());
            fclose($fp);
            return false;
        }
        fwrite($fp, base64_encode($user) . "\r\n");
        if (!self::smtpExpect($fp, [334])) {
            self::smtpLog($logFile, 'SMTP AUTH user rejected: ' . self::smtpLastResponse());
            fclose($fp);
            return false;
        }
        fwrite($fp, base64_encode($pass) . "\r\n");
        if (!self::smtpExpect($fp, [235])) {
            self::smtpLog($logFile, 'SMTP AUTH failed: ' . self::smtpLastResponse());
            fclose($fp);
            return false;
        }

        $fromAddr = self::extractAddr($from);
        fwrite($fp, 'MAIL FROM:<' . $fromAddr . ">\r\n");
        if (!self::smtpExpect($fp, [250])) {
            self::smtpLog($logFile, 'SMTP MAIL FROM failed: ' . self::smtpLastResponse());
            fclose($fp);
            return false;
        }

        fwrite($fp, 'RCPT TO:<' . $to . ">\r\n");
        if (!self::smtpExpect($fp, [250, 251])) {
            self::smtpLog($logFile, 'SMTP RCPT TO failed: ' . self::smtpLastResponse());
            fclose($fp);
            return false;
        }

        fwrite($fp, "DATA\r\n");
        if (!self::smtpExpect($fp, [354])) {
            self::smtpLog($logFile, 'SMTP DATA not accepted: ' . self::smtpLastResponse());
            fclose($fp);
            return false;
        }

        $encSubject = self::encodeSubject($subject);
        $headers = [
            'Date: ' . gmdate('D, d M Y H:i:s') . ' +0000',
            'From: ' . $from,
            'To: ' . $to,
            'Subject: ' . $encSubject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        $bodyNorm = str_replace(["\r\n", "\r"], "\n", $body);
        $bodyNorm = preg_replace('/^\./m', '..', $bodyNorm) ?? $bodyNorm;
        $bodyNorm = str_replace("\n", "\r\n", $bodyNorm);

        $data = implode("\r\n", $headers) . "\r\n\r\n" . $bodyNorm . "\r\n.\r\n";
        fwrite($fp, $data);
        if (!self::smtpExpect($fp, [250])) {
            self::smtpLog($logFile, 'SMTP message rejected: ' . self::smtpLastResponse());
            fclose($fp);
            return false;
        }

        fwrite($fp, "QUIT\r\n");
        fclose($fp);
        return true;
    }

    private static string $lastSmtpResponse = '';

    private static function smtpRead($fp): string
    {
        $all = '';
        while (($line = fgets($fp, 2048)) !== false) {
            $all .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        return $all;
    }

    private static function smtpExpect($fp, array $codes): bool
    {
        self::$lastSmtpResponse = self::smtpRead($fp);
        if (strlen(self::$lastSmtpResponse) < 3) {
            return false;
        }
        $code = (int) substr(self::$lastSmtpResponse, 0, 3);
        return in_array($code, $codes, true);
    }

    private static function smtpLastResponse(): string
    {
        return trim(str_replace(["\r\n", "\r", "\n"], ' | ', self::$lastSmtpResponse));
    }

    private static function smtpEhloHost(): string
    {
        $d = self::conf('mail_ehlo_domain', 'MAIL_EHLO_DOMAIN', '');
        if ($d !== '') {
            return $d;
        }
        $from = self::mailFromAddress();
        if ($from !== '' && preg_match('/@([a-z0-9.-]+)/i', $from, $m)) {
            return $m[1];
        }
        return 'localhost';
    }

    private static function extractAddr(string $from): string
    {
        if (preg_match('/<([^>]+)>/', $from, $m)) {
            return trim($m[1]);
        }
        return trim($from);
    }

    private static function encodeSubject(string $subject): string
    {
        if (!preg_match('/[^\x20-\x7E]/', $subject)) {
            return $subject;
        }
        if (function_exists('mb_encode_mimeheader')) {
            return mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n");
        }
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }

    private static function smtpLog(string $logFile, string $message): void
    {
        @file_put_contents($logFile, date('c') . ' | ' . $message . "\n", FILE_APPEND | LOCK_EX);
    }
}
