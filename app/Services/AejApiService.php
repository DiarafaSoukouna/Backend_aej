<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\DTO\TypePieceIdentiteDTO;
use App\DTO\SituationMatrimonialeDTO;
use App\DTO\SecteurDTO;
use App\DTO\SousSecteurDTO;
use App\DTO\NiveauEtudeDTO;
use App\DTO\AgenceRegionaleDTO;
use App\DTO\ProjetParameterDTO;
use App\DTO\SexeDTO;
use App\DTO\LieuHabitationDTO;
use App\DTO\PaysDTO;
use App\DTO\TypeSituationHandicapDTO;
use App\DTO\CommuneDTO;
use App\DTO\DivisionRegionaleDTO;
use App\DTO\VilleDTO;
use App\Exceptions\AejApiException;
use App\Exceptions\AejApiTimeoutException;
use App\Exceptions\AejApiUnavailableException;
use App\Exceptions\AejApiAuthenticationException;
use App\Exceptions\AejApiNotFoundException;
use App\Exceptions\AejApiValidationException;
use App\Exceptions\AejApiRateLimitException;
use App\Exceptions\AejApiServerException;

class AejApiService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected int $timeout;
    protected int $retry;
    protected int $retryDelay;
    protected bool $cacheEnabled;
    protected int $cacheTtl;
    protected string $cachePrefix;

    public function __construct()
    {
        $config = config('aej_api');
        
        $this->baseUrl = $config['base_url'];
        $this->apiKey = $config['api_key'];
        $this->timeout = $config['timeout'];
        $this->retry = $config['retry'];
        $this->retryDelay = $config['retry_delay'];
        $this->cacheEnabled = $config['cache']['enabled'];
        $this->cacheTtl = $config['cache']['ttl'];
        $this->cachePrefix = $config['cache']['prefix'];
    }

    protected function makeRequest(string $endpoint): array
    {
        $cacheKey = $this->cachePrefix . md5($endpoint);

        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->retry($this->retry, $this->retryDelay)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->when($this->apiKey, fn($http) => $http->withToken($this->apiKey))
                ->get($endpoint);

            $this->handleResponseErrors($response);

            $data = $response->json();

            if ($this->cacheEnabled) {
                Cache::put($cacheKey, $data, $this->cacheTtl);
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('AEJ API Connection Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            throw new AejApiUnavailableException('API unavailable: ' . $e->getMessage(), 0, $e);

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'timeout') || str_contains($e->getMessage(), 'timed out')) {
                Log::error('AEJ API Timeout', [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);
                throw new AejApiTimeoutException('API request timeout', 0, $e);
            }
            
            Log::error('AEJ API Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            throw new AejApiException('API error: ' . $e->getMessage(), 0, $e);
        }
    }

    protected function handleResponseErrors($response): void
    {
        if ($response->successful()) {
            return;
        }

        $status = $response->status();
        $body = $response->body();

        Log::error('AEJ API Response Error', [
            'status' => $status,
            'body' => $body,
        ]);

        match ($status) {
            401 => throw new AejApiAuthenticationException('Authentication failed'),
            403 => throw new AejApiAuthenticationException('Access forbidden'),
            404 => throw new AejApiNotFoundException('Resource not found'),
            422 => throw new AejApiValidationException('Validation error: ' . $body),
            429 => throw new AejApiRateLimitException('Rate limit exceeded'),
            500, 502, 503, 504 => throw new AejApiServerException('Server error'),
            default => throw new AejApiException("API error: HTTP {$status}"),
        };
    }

    public function getTypesPiecesIdentites(): array
    {
        $endpoint = config('aej_api.endpoints.types_pieces_identites');
        $data = $this->makeRequest($endpoint);
        return TypePieceIdentiteDTO::fromArrayCollection($data);
    }

    public function getSituationsMatrimoniale(): array
    {
        $endpoint = config('aej_api.endpoints.situations_matrimoniale');
        $data = $this->makeRequest($endpoint);
        return SituationMatrimonialeDTO::fromArrayCollection($data);
    }

    public function getSecteurs(): array
    {
        $endpoint = config('aej_api.endpoints.secteurs');
        $data = $this->makeRequest($endpoint);
        return SecteurDTO::fromArrayCollection($data);
    }

    public function getSousSecteurs(): array
    {
        $endpoint = config('aej_api.endpoints.sous_secteurs');
        $data = $this->makeRequest($endpoint);
        return SousSecteurDTO::fromArrayCollection($data);
    }

    public function getNiveauxEtudes(): array
    {
        $endpoint = config('aej_api.endpoints.niveaux_etudes');
        $data = $this->makeRequest($endpoint);
        return NiveauEtudeDTO::fromArrayCollection($data);
    }

    public function getAgencesRegionales(): array
    {
        $endpoint = config('aej_api.endpoints.agences_regionales');
        $data = $this->makeRequest($endpoint);
        return AgenceRegionaleDTO::fromArrayCollection($data);
    }

    public function getAllReferentiels(): array
    {
        return [
            'types_pieces_identites' => $this->getTypesPiecesIdentites(),
            'situations_matrimoniale' => $this->getSituationsMatrimoniale(),
            'secteurs' => $this->getSecteurs(),
            'sous_secteurs' => $this->getSousSecteurs(),
            'niveaux_etudes' => $this->getNiveauxEtudes(),
            'agences_regionales' => $this->getAgencesRegionales(),
            'sexes' => $this->getSexes(),
            'lieu_habitations' => $this->getLieuHabitations(),
            'pays' => $this->getPays(),
            'situations_handicaps' => $this->getSituationsHandicaps(),
            'communes' => $this->getCommunes(),
            'division_regionale' => $this->getDivisionRegionale(),
            'villes' => $this->getVilles(),
        ];
    }

    public function getSexes(): array
    {
        $endpoint = config('aej_api.endpoints.sexes');
        $data = $this->makeRequest($endpoint);
        return SexeDTO::fromArrayCollection($data);
    }

    public function getLieuHabitations(): array
    {
        $endpoint = config('aej_api.endpoints.lieu_habitations');
        $data = $this->makeRequest($endpoint);
        return LieuHabitationDTO::fromArrayCollection($data);
    }

    public function getPays(): array
    {
        $endpoint = config('aej_api.endpoints.pays');
        $data = $this->makeRequest($endpoint);
        return PaysDTO::fromArrayCollection($data);
    }

    public function getSituationsHandicaps(): array
    {
        $endpoint = config('aej_api.endpoints.situations_handicaps');
        $data = $this->makeRequest($endpoint);
        return TypeSituationHandicapDTO::fromArrayCollection($data);
    }

    public function getCommunes(): array
    {
        $endpoint = config('aej_api.endpoints.communes');
        $data = $this->makeRequest($endpoint);
        return CommuneDTO::fromArrayCollection($data);
    }

    public function getDivisionRegionale(): array
    {
        $endpoint = config('aej_api.endpoints.projet_parameters');
        $data = $this->makeRequest($endpoint);
        $apiData = $data['parameter'] ?? [];
        $divisions = $apiData['divisions'] ?? [];
        return DivisionRegionaleDTO::fromArrayCollection($divisions);
    }

    public function getVilles(): array
    {
        $endpoint = config('aej_api.endpoints.projet_parameters');
        $data = $this->makeRequest($endpoint);
        $apiData = $data['parameter'] ?? [];
        $villes = $apiData['villes'] ?? [];
        return VilleDTO::fromArrayCollection($villes);
    }

    public function clearCache(): void
    {
        if ($this->cacheEnabled) {
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.types_pieces_identites')));
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.situations_matrimoniale')));
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.secteurs')));
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.sous_secteurs')));
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.niveaux_etudes')));
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.agences_regionales')));
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.projet_parameters')));
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.sexes')));
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.lieu_habitations')));
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.pays')));
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.situations_handicaps')));
            Cache::forget($this->cachePrefix . md5(config('aej_api.endpoints.communes')));
        }
    }
}
