<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $deviceId;

    public function __construct()
    {
        $this->apiUrl = config('services.green_api.url', 'https://api.green-api.com');
        $this->apiKey = config('services.green_api.api_key');
        $this->deviceId = config('services.green_api.device_id');
    }

    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $formattedPhone = $this->formatPhoneNumber($phone);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/waInstance{$this->deviceId}/sendMessage/{$this->apiKey}", [
                'chatId' => $formattedPhone . '@c.us',
                'message' => $message
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'phone' => $formattedPhone,
                    'response' => $response->json()
                ]);
                return true;
            }

            Log::error('Failed to send WhatsApp message', [
                'phone' => $formattedPhone,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp service error', [
                'error' => $e->getMessage(),
                'phone' => $phone
            ]);
            return false;
        }
    }

    public function sendOtp(string $phone, string $otp): bool
    {
        $message = "Votre code de vérification AEJ est: {$otp}\n\nCe code expire dans 15 minutes. Ne le partagez avec personne.";

        return $this->sendMessage($phone, $message);
    }

    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) $phone = substr($phone, 1);

        return $phone;
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->deviceId);
    }
}
