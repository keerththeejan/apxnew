<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WhatsAppLog;

final class WhatsAppService
{
    public static function isEnabled(): bool
    {
        return SiteConfig::get('whatsapp_enabled', '0') === '1';
    }

    public static function formatPhone(string $phone, string $defaultCountryCode = '94'): string
    {
        $p = preg_replace('/[^0-9+]/', '', trim($phone)) ?? '';
        if ($p === '') {
            return '';
        }
        if (str_starts_with($p, '00')) {
            $p = '+' . substr($p, 2);
        }
        if (!str_starts_with($p, '+')) {
            $p = ltrim($p, '0');
            if ($p === '') {
                return '';
            }
            $cc = preg_replace('/[^0-9]/', '', $defaultCountryCode) ?? '94';
            $p = '+' . $cc . $p;
        }

        $digits = preg_replace('/\D/', '', $p) ?? '';
        if (strlen($digits) < 8 || strlen($digits) > 15) {
            return '';
        }

        return '+' . $digits;
    }

    public static function clickToChatUrl(string $phone, string $message): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
    }

    /**
     * @param array<string, string> $vars
     */
    public static function renderTemplate(string $templateKey, array $vars): string
    {
        $tpl = trim(SiteConfig::get($templateKey, ''));
        if ($tpl === '') {
            $defaults = [
                'whatsapp_tpl_new_order' => 'Hello {{name}}, your booking {{code}} has been received.',
                'whatsapp_tpl_status_update' => 'Hello {{name}}, your status is now {{status}}.',
                'whatsapp_tpl_service_info' => 'Hello {{name}}, here is the service info: {{service}}',
            ];
            $tpl = $defaults[$templateKey] ?? 'Hello {{name}}, thank you for contacting APX.';
        }

        $out = $tpl;
        foreach ($vars as $k => $v) {
            $out = str_replace('{{' . $k . '}}', trim((string) $v), $out);
        }

        return trim($out);
    }

    /**
     * @return array{ok:bool,provider:string,message:string,http_code:int,click_url:string}
     */
    public static function sendText(string $phone, string $message, string $context = '', ?int $entityId = null): array
    {
        $formatted = self::formatPhone($phone, SiteConfig::get('whatsapp_country_code', '94'));
        if (!self::isEnabled()) {
            $url = self::clickToChatUrl($formatted !== '' ? $formatted : $phone, $message);
            self::log('skipped', 'disabled', $formatted, $message, $context, $entityId, 0, $url);

            return ['ok' => false, 'provider' => 'disabled', 'message' => 'WhatsApp is disabled.', 'http_code' => 0, 'click_url' => $url];
        }
        if ($formatted === '') {
            self::log('failed', 'invalid_phone', $phone, $message, $context, $entityId, 0, '');

            return ['ok' => false, 'provider' => 'validation', 'message' => 'Invalid phone number.', 'http_code' => 422, 'click_url' => ''];
        }

        $token = trim((string) env('WHATSAPP_API_TOKEN', SiteConfig::get('whatsapp_api_token', '')));
        $phoneId = trim((string) env('WHATSAPP_PHONE_NUMBER_ID', SiteConfig::get('whatsapp_phone_number_id', '')));
        if ($token === '' || $phoneId === '') {
            $url = self::clickToChatUrl($formatted, $message);
            self::log('sent', 'click_to_chat', $formatted, $message, $context, $entityId, 200, $url);

            return ['ok' => true, 'provider' => 'click_to_chat', 'message' => 'Prepared WhatsApp Click-to-Chat link.', 'http_code' => 200, 'click_url' => $url];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => ltrim($formatted, '+'),
            'type' => 'text',
            'text' => ['body' => $message],
        ];
        $url = 'https://graph.facebook.com/v18.0/' . rawurlencode($phoneId) . '/messages';
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            self::log('failed', 'encode_error', $formatted, $message, $context, $entityId, 0, '');

            return ['ok' => false, 'provider' => 'cloud_api', 'message' => 'Could not encode payload.', 'http_code' => 0, 'click_url' => ''];
        }

        $result = self::postJson($url, $json, $token);
        $ok = $result['ok'];
        self::log($ok ? 'sent' : 'failed', $ok ? 'cloud_api' : 'cloud_api_error', $formatted, $message, $context, $entityId, $result['http_code'], $result['body']);

        return [
            'ok' => $ok,
            'provider' => 'cloud_api',
            'message' => $ok ? 'Message sent.' : 'Cloud API request failed.',
            'http_code' => $result['http_code'],
            'click_url' => '',
        ];
    }

    /**
     * @return array{ok:bool,http_code:int,body:string}
     */
    private static function postJson(string $url, string $json, string $token): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return ['ok' => false, 'http_code' => 0, 'body' => 'curl_init failed'];
            }
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => $json,
            ]);
            $resp = curl_exec($ch);
            $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            if ($resp === false) {
                return ['ok' => false, 'http_code' => $http, 'body' => $err];
            }

            return ['ok' => $http >= 200 && $http < 300, 'http_code' => $http, 'body' => (string) $resp];
        }

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => 12,
                'header' => "Authorization: Bearer {$token}\r\nContent-Type: application/json\r\n",
                'content' => $json,
                'ignore_errors' => true,
            ],
        ]);
        $resp = @file_get_contents($url, false, $ctx);
        $http = 0;
        if (isset($http_response_header) && is_array($http_response_header) && isset($http_response_header[0])) {
            if (preg_match('/\s(\d{3})\s/', (string) $http_response_header[0], $m)) {
                $http = (int) $m[1];
            }
        }
        $body = is_string($resp) ? $resp : '';

        return ['ok' => $http >= 200 && $http < 300, 'http_code' => $http, 'body' => $body];
    }

    private static function log(string $status, string $provider, string $toPhone, string $message, string $context, ?int $entityId, int $httpCode, string $response): void
    {
        try {
            WhatsAppLog::create([
                'status' => $status,
                'provider' => $provider,
                'to_phone' => $toPhone,
                'message_body' => $message,
                'context_key' => $context,
                'entity_id' => $entityId,
                'http_code' => $httpCode,
                'response_body' => $response,
            ]);
        } catch (\Throwable) {
        }
    }
}

