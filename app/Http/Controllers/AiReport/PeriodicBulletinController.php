<?php

namespace App\Http\Controllers\AiReport;

use App\Http\Controllers\Controller;
use App\Models\AiReportLog;
use App\Models\PeriodicBulletin;
use App\Services\Ai\AiReportService;
use App\Services\Report\KpiCalculatorService;
use App\Services\Report\PdfReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Module 3 — Bulletins périodiques (mensuel/trimestriel).
 * Peut être déclenché manuellement ou automatiquement via le scheduler.
 */
class PeriodicBulletinController extends Controller
{
    public function __construct(
        private AiReportService      $aiService,
        private KpiCalculatorService $kpiService,
        private PdfReportService     $pdfService,
    ) {}

    /**
     * GET /api/ai-reports/bulletins
     * Liste des bulletins générés avec pagination.
     */
    public function index(Request $request)
    {
        $bulletins = PeriodicBulletin::with('organisme', 'generePar')
            ->when($request->input('periode_type'), fn($q, $v) => $q->where('periode_type', $v))
            ->when($request->input('organisme_id'), fn($q, $v) => $q->where('organisme_id', $v))
            ->orderByDesc('periode_debut')
            ->paginate(20);

        return response()->json($bulletins);
    }

    /**
     * POST /api/ai-reports/bulletins
     * Génère un bulletin périodique manuellement.
     *
     * Body JSON :
     * {
     *   "periode_type": "mensuel",        // ou "trimestriel"
     *   "periode_debut": "2025-01-01",
     *   "periode_fin":   "2025-01-31",
     *   "organisme_id":  null             // null = global
     * }
     */
    public function generate(Request $request)
    {
        $request->validate([
            'periode_type'  => 'required|in:mensuel,trimestriel',
            'periode_debut' => 'required|date',
            'periode_fin'   => 'required|date|after_or_equal:periode_debut',
            'organisme_id'  => 'nullable|integer|exists:organisme_financements,id',
        ]);

        $start       = microtime(true);
        $personnelId = auth()->id();

        try {
            $bulletin = $this->generateBulletin(
                periodeType:  $request->input('periode_type'),
                debut:        $request->input('periode_debut'),
                fin:          $request->input('periode_fin'),
                organismeId:  $request->input('organisme_id'),
                personnelId:  $personnelId,
                auto:         false
            );

            $duration = (int) round((microtime(true) - $start) * 1000);

            return response()->json([
                'message'      => 'Bulletin généré avec succès',
                'bulletin'     => $bulletin,
                'pdf_url'      => $bulletin->pdf_path ? url('storage/' . $bulletin->pdf_path) : null,
                'duree_ms'     => $duration,
            ], 201);
        } catch (\Throwable $e) {
            AiReportLog::logError('bulletin', $personnelId, $e->getMessage());
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/ai-reports/bulletins/{id}
     * Détail d'un bulletin.
     */
    public function show(int $id)
    {
        $bulletin = PeriodicBulletin::with('organisme', 'generePar')->findOrFail($id);
        return response()->json($bulletin);
    }

    /**
     * GET /api/ai-reports/bulletins/{id}/download
     * Télécharge le PDF d'un bulletin.
     */
    public function download(int $id)
    {
        $bulletin = PeriodicBulletin::findOrFail($id);

        if (!$bulletin->pdf_path || !Storage::disk('public')->exists($bulletin->pdf_path)) {
            return response()->json(['message' => 'Fichier PDF non trouvé'], 404);
        }

        return Storage::disk('public')->download($bulletin->pdf_path, basename($bulletin->pdf_path));
    }

    // =========================================================================
    // Logique de génération (partagée avec le Job scheduler)
    // =========================================================================

    /**
     * Génère un bulletin complet et le persiste en BDD.
     * Méthode publique car appelée aussi par GeneratePeriodicBulletinJob.
     */
    public function generateBulletin(
        string $periodeType,
        string $debut,
        string $fin,
        ?int   $organismeId = null,
        ?int   $personnelId = null,
        bool   $auto = false
    ): PeriodicBulletin {
        // 1. Calculer les KPIs (déterministe)
        $kpis = $this->kpiService->globalKpis($organismeId, $debut, $fin);

        // 2. Catégorisation des payeurs
        $categorisation = $this->kpiService->payerCategorization($organismeId, $debut, $fin);

        // 3. Commentaire IA
        $periodeLabel  = Carbon::parse($debut)->isoFormat('MMMM YYYY') . ' – ' . Carbon::parse($fin)->isoFormat('MMMM YYYY');
        $contexte      = $periodeType === 'trimestriel' ? 'Rapport trimestriel' : 'Rapport mensuel';
        $aiCommentaire = $this->aiService->generateBulletinNarrative($kpis, $contexte, $periodeLabel);

        // 4. Créer le bulletin en BDD
        $bulletin = PeriodicBulletin::create([
            'periode_type'           => $periodeType,
            'periode_debut'          => $debut,
            'periode_fin'            => $fin,
            'organisme_id'           => $organismeId,
            'kpis'                   => $kpis,
            'commentaire_ia'         => $aiCommentaire,
            'categorisation_payeurs' => $categorisation,
            'genere_par'             => $personnelId,
            'genere_auto'            => $auto,
        ]);

        // 5. Générer le PDF
        $pdfPath = $this->pdfService->generateBulletin($bulletin);
        $bulletin->update(['pdf_path' => $pdfPath]);

        // 6. Journaliser
        AiReportLog::logSuccess('bulletin', $personnelId, [
            'sujet_id'   => $organismeId,
            'sujet_type' => 'bulletin',
            'reponse_ia' => $aiCommentaire,
            'pdf_path'   => $pdfPath,
        ]);

        return $bulletin->fresh();
    }
}
