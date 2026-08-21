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

    public function checkStatus(): array
    {
        if (!$this->isConfigured()) {
            return ['status' => 'notConfigured', 'canSend' => false];
        }

        try {
            $response = Http::get("{$this->apiUrl}/waInstance{$this->deviceId}/getState/{$this->apiKey}");

            if ($response->successful()) {
                $state = $response->json('state', 'unknown');
                return [
                    'status' => $state,
                    'canSend' => in_array($state, ['authorized', 'serviceReady']),
                    'message' => $this->getStatusMessage($state)
                ];
            }

            return ['status' => 'error', 'canSend' => false];
        } catch (\Exception $e) {
            Log::error('WhatsApp status error', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'canSend' => false];
        }
    }

    protected function getStatusMessage(string $state): string
    {
        return match($state) {
            'authorized', 'serviceReady' => 'Service opérationnel',
            'suspended' => 'Service suspendu',
            'blocked' => 'Service bloqué',
            'notAuthorized' => 'Reconnexion requise',
            default => 'État inconnu'
        };
    }

    public function sendMessage(string $phone, string $message): bool
    {
        if (!$this->isConfigured()) return false;

        $status = $this->checkStatus();
        if (!$status['canSend']) {
            Log::warning('WhatsApp cannot send', ['status' => $status['status']]);
            return false;
        }

        try {
            $response = Http::post("{$this->apiUrl}/waInstance{$this->deviceId}/sendMessage/{$this->apiKey}", [
                'chatId' => $this->formatPhoneNumber($phone) . '@c.us',
                'message' => $message
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp send error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function sendOtp(string $phone, string $otp): bool
    {
        return $this->sendMessage($phone, "Votre code AEJ: {$otp}");
    }

    public function reconnect(): bool
    {
        try {
            $response = Http::get("{$this->apiUrl}/waInstance{$this->deviceId}/reconnect/{$this->apiKey}");
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp reconnect error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getQRCode(): array
    {
        try {
            $response = Http::get("{$this->apiUrl}/waInstance{$this->deviceId}/qr/{$this->apiKey}");
            return $response->successful() 
                ? ['success' => true, 'data' => $response->json()]
                : ['success' => false];
        } catch (\Exception $e) {
            return ['success' => false];
        }
    }

    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return str_starts_with($phone, '0') ? substr($phone, 1) : $phone;
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->deviceId);
    }
}
