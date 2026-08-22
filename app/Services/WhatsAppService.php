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
        
        Log::info('WhatsAppService initialized', [
            'apiUrl' => $this->apiUrl,
            'deviceId' => $this->deviceId,
            'configured' => $this->isConfigured()
        ]);
    }

    public function checkStatus(): array
    {
        if (!$this->isConfigured()) {
            return [
                'status' => 'notConfigured', 
                'canSend' => false,
                'message' => 'Service WhatsApp non configuré'
            ];
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

            return [
                'status' => 'error', 
                'canSend' => false,
                'message' => 'Impossible de vérifier l\'état du service'
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp status error', ['error' => $e->getMessage()]);
            return [
                'status' => 'error', 
                'canSend' => false,
                'message' => 'Erreur lors de la vérification du statut'
            ];
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
        try {
            $formattedPhone = $this->formatPhoneNumber($phone);
            Log::info('Attempting to send WhatsApp message', [
                'original_phone' => $phone,
                'formatted_phone' => $formattedPhone,
                'chatId' => $formattedPhone . '@c.us',
                'message_length' => strlen($message),
                'api_url' => $this->apiUrl,
                'device_id' => $this->deviceId
            ]);

            $response = Http::timeout(30)->post("{$this->apiUrl}/waInstance{$this->deviceId}/sendMessage/{$this->apiKey}", [
                'chatId' => $formattedPhone . '@c.us',
                'message' => $message
            ]);

            Log::info('WhatsApp API response', [
                'successful' => $response->successful(),
                'status' => $response->status(),
                'body' => $response->body(),
                'json' => $response->json()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['error']) && $responseData['error']) {
                    Log::error('WhatsApp API returned error', [
                        'error' => $responseData['error'],
                        'error_code' => $responseData['error_code'] ?? null
                    ]);
                    return false;
                }
                
                Log::info('WhatsApp message sent successfully', [
                    'phone' => $formattedPhone,
                    'response' => $responseData
                ]);
                return true;
            }

            Log::error('Failed to send WhatsApp message', [
                'phone' => $formattedPhone,
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp send error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function testConnection(): array
    {
        $result = [
            'configured' => $this->isConfigured(),
            'api_url' => $this->apiUrl,
            'device_id' => $this->deviceId,
            'has_api_key' => !empty($this->apiKey),
            'status' => null,
            'message' => null
        ];

        if (!$this->isConfigured()) {
            $result['message'] = 'Service non configuré';
            return $result;
        }

        $status = $this->checkStatus();
        $result['status'] = $status['status'];
        $result['message'] = $status['message'];
        $result['can_send'] = $status['canSend'];

        return $result;
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
        if (str_starts_with($phone, '0'))  $phone = '225' . substr($phone, 1);
        return $phone;
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->deviceId);
    }
}
