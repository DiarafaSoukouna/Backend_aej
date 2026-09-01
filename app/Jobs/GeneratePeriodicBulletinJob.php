<?php

namespace App\Jobs;

use App\Http\Controllers\AiReport\PeriodicBulletinController;
use App\Services\Ai\AiReportService;
use App\Services\Report\KpiCalculatorService;
use App\Services\Report\PdfReportService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job de génération automatique des bulletins périodiques.
 * Déclenché par le scheduler dans routes/console.php.
 */
class GeneratePeriodicBulletinJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes max par job
    public int $tries   = 2;   // 2 tentatives en cas d'échec

    public function __construct(
        private readonly string $periodeType,       // 'mensuel' | 'trimestriel'
        private readonly string $debut,             // date ISO
        private readonly string $fin,               // date ISO
        private readonly ?int   $organismeId = null // null = global
    ) {}

    public function handle(
        AiReportService      $aiService,
        KpiCalculatorService $kpiService,
        PdfReportService     $pdfService
    ): void {
        Log::info("GeneratePeriodicBulletinJob: démarrage", [
            'periode_type' => $this->periodeType,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'organisme_id' => $this->organismeId,
        ]);

        try {
            $controller = new PeriodicBulletinController($aiService, $kpiService, $pdfService);

            $bulletin = $controller->generateBulletin(
                periodeType: $this->periodeType,
                debut:       $this->debut,
                fin:         $this->fin,
                organismeId: $this->organismeId,
                personnelId: null,  // généré automatiquement (pas de personnel associé)
                auto:        true
            );

            Log::info("GeneratePeriodicBulletinJob: succès", [
                'bulletin_id' => $bulletin->id,
                'pdf_path'    => $bulletin->pdf_path,
            ]);
        } catch (\Throwable $e) {
            Log::error("GeneratePeriodicBulletinJob: échec", [
                'error'        => $e->getMessage(),
                'periode_type' => $this->periodeType,
                'debut'        => $this->debut,
            ]);
            throw $e;
        }
    }

    /**
     * Dispatch le job pour le mois précédent (usage scheduler mensuel).
     */
    public static function forLastMonth(?int $organismeId = null): self
    {
        $debut = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $fin   = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        return new self('mensuel', $debut, $fin, $organismeId);
    }

    /**
     * Dispatch le job pour le trimestre précédent (usage scheduler trimestriel).
     */
    public static function forLastQuarter(?int $organismeId = null): self
    {
        $now   = Carbon::now();
        $debut = $now->copy()->subQuarter()->startOfQuarter()->toDateString();
        $fin   = $now->copy()->subQuarter()->endOfQuarter()->toDateString();

        return new self('trimestriel', $debut, $fin, $organismeId);
    }
}
