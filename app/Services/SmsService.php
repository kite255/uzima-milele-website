<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(?string $phone, string $message): bool
    {
        $phone = $this->formatPhone($phone);

        if (! $phone) {
            Log::warning('SMS not sent: invalid or missing phone number.');
            return false;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'api_key' => config('sms.api_key'),
                    'api_secret' => config('sms.api_secret'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post(config('sms.url'), [
                    'senderId' => config('sms.sender_id'),
                    'messageType' => 'text',
                    'message' => $message,
                    'contacts' => $phone,
                    'deliveryReportUrl' => url('/api/sms/delivery-callback'),
                ]);

            if ($response->successful()) {
                Log::info('SMS sent successfully', [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return true;
            }

            Log::error('SMS sending failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('SMS exception', [
                'phone' => $phone,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function formatPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '255' . substr($phone, 1);
        }

        if (str_starts_with($phone, '255')) {
            return $phone;
        }

        if (strlen($phone) === 9) {
            return '255' . $phone;
        }

        return null;
    }
}