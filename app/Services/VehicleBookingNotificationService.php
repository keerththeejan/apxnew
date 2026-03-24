<?php

declare(strict_types=1);

namespace App\Services;

final class VehicleBookingNotificationService
{
    /**
     * @param array<string,mixed> $booking
     */
    public static function sendForEvent(array $booking, string $event): void
    {
        $phone = trim((string) ($booking['customer_phone'] ?? ''));
        $email = trim((string) ($booking['customer_email'] ?? ''));
        if ($phone === '' && $email === '') {
            return;
        }

        $vars = [
            'name' => (string) ($booking['customer_name'] ?? 'Customer'),
            'ref' => (string) ($booking['booking_ref'] ?? ''),
            'status' => (string) ($booking['status'] ?? 'pending'),
            'pickup' => (string) ($booking['pickup_location'] ?? ''),
            'drop' => (string) ($booking['drop_location'] ?? ''),
            'date' => (string) ($booking['pickup_datetime'] ?? ''),
            'total' => (string) ($booking['estimated_total'] ?? '0'),
            'vehicle_type' => (string) ($booking['vehicle_type'] ?? ''),
        ];

        $templateKey = match ($event) {
            'created' => 'whatsapp_tpl_vehicle_booking_created',
            'assigned' => 'whatsapp_tpl_vehicle_booking_assigned',
            'on_trip' => 'whatsapp_tpl_vehicle_booking_started',
            'completed' => 'whatsapp_tpl_vehicle_booking_completed',
            default => 'whatsapp_tpl_vehicle_booking_status_update',
        };

        $default = "Hello {{name}}, your booking {{ref}} is {{status}}.\nPickup: {{pickup}}\nDate: {{date}}\nTotal: {{total}}";
        $tpl = trim(SiteConfig::get($templateKey, $default));
        if ($tpl === '') {
            $tpl = $default;
        }
        $message = $tpl;
        foreach ($vars as $k => $v) {
            $message = str_replace('{{' . $k . '}}', trim((string) $v), $message);
        }

        if ($phone !== '') {
            WhatsAppService::sendText($phone, $message, 'vehicle_booking.' . $event, isset($booking['id']) ? (int) $booking['id'] : null);
        }
        if ($email !== '') {
            self::sendEmail($email, 'Vehicle booking update', $message);
        }
    }

    private static function sendEmail(string $to, string $subject, string $body): void
    {
        $to = trim($to);
        if ($to === '') {
            return;
        }
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/plain; charset=UTF-8',
            'From: no-reply@apx.local',
        ];
        try {
            @mail($to, $subject, $body, implode("\r\n", $headers));
        } catch (\Throwable) {
        }
    }
}
