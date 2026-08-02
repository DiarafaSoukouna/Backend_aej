<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\AejApiService;

class AejApiTest extends TestCase
{
    public function test_get_types_pieces_identites(): void
    {
        Http::fake([
            'agenceemploijeunes.ci/*' => Http::response([
                [
                    'id' => 1,
                    'libelle' => 'CNI ORANGE',
                    'description' => null,
                    'created_at' => '2026-04-13T12:48:36.000000Z',
                    'updated_at' => '2026-04-13T12:48:36.000000Z',
                    'migration_key' => null,
                    'actif' => 1,
                ]
            ], 200),
        ]);

        $service = app(AejApiService::class);
        $result = $service->getTypesPiecesIdentites();

        $this->assertCount(1, $result);
        $this->assertEquals('CNI ORANGE', $result[0]->libelle);
    }

    public function test_get_situations_matrimoniale(): void
    {
        Http::fake([
            'agenceemploijeunes.ci/*' => Http::response([
                ['id' => 1, 'libelle' => 'MARIE(E)'],
            ], 200),
        ]);

        $service = app(AejApiService::class);
        $result = $service->getSituationsMatrimoniale();

        $this->assertCount(1, $result);
        $this->assertEquals('MARIE(E)', $result[0]->libelle);
    }

    public function test_get_secteurs(): void
    {
        Http::fake([
            'agenceemploijeunes.ci/*' => Http::response([
                ['id' => 1, 'libelle' => 'ADMINISTRATION', 'nom' => 'ADMINISTRATION'],
            ], 200),
        ]);

        $service = app(AejApiService::class);
        $result = $service->getSecteurs();

        $this->assertCount(1, $result);
        $this->assertEquals('ADMINISTRATION', $result[0]->libelle);
    }

    public function test_api_timeout_exception(): void
    {
        Http::fake([
            'agenceemploijeunes.ci/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
            },
        ]);

        $this->expectException(\App\Exceptions\AejApiUnavailableException::class);

        $service = app(AejApiService::class);
        $service->getTypesPiecesIdentites();
    }

    public function test_api_404_exception(): void
    {
        Http::fake([
            'agenceemploijeunes.ci/*' => Http::response([], 404),
        ]);

        $this->expectException(\App\Exceptions\AejApiNotFoundException::class);

        $service = app(AejApiService::class);
        $service->getTypesPiecesIdentites();
    }

    public function test_api_500_exception(): void
    {
        Http::fake([
            'agenceemploijeunes.ci/*' => Http::response([], 500),
        ]);

        $this->expectException(\App\Exceptions\AejApiServerException::class);

        $service = app(AejApiService::class);
        $service->getTypesPiecesIdentites();
    }
}
