<?php

namespace App\Http\Controllers\AiReport;

use App\Http\Controllers\Controller;
use App\Models\AiReportLog;
use App\Services\Ai\AiReportService;
use App\Services\Report\KpiCalculatorService;
use App\Services\Report\PdfReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Module 2 — Rapports globaux par organisme ou transversaux.
 */
class GlobalReportController extends Controller
{
    public function __construct(
        private AiReportService      $aiService,
        private KpiCalculatorService $kpiService,
        private PdfReportService     $pdfService,
    ) {}

    /**
     * POST /api/ai-reports/global
     * Génère un rapport global pour un organisme ou transversal.
     *
     * Body JSON :
     * {
     *   "organisme_id": 1,       // null = global
     *   "date_debut": "2025-01-01",
     *   "date_fin": "2025-12-31"
     * }
     */
    public function generate(Request $request)
    {
        $request->validate([
            'organisme_id' => 'nullable|integer|exists:organisme_financements,id',
            'date_debut'   => 'required|date',
            'date_fin'     => 'required|date|after_or_equal:date_debut',
        ]);

        $start        = microtime(true);
        $personnelId  = auth()->id();
        $organismeId  = $request->input('organisme_id');
        $debut        = $request->input('date_debut');
        $fin          = $request->input('date_fin');

        try {
            // Nom de l'organisme si filtré
            $organismeNom = null;
            if ($organismeId) {
                $org          = DB::table('organisme_financements')->where('id', $organismeId)->first();
                $organismeNom = $org?->nom;
            }

            // 1. KPIs globaux (déterministe)
            $kpis = $this->kpiService->globalKpis($organismeId, $debut, $fin);

            // 2. Analyse IA
            $aiAnalysis = $this->aiService->generateGlobalAnalysis($kpis, $organismeNom);

            // 3. PDF
            $pdfPath = $this->pdfService->generateGlobalReport($kpis, $aiAnalysis, $organismeNom);

            $duration = (int) round((microtime(true) - $start) * 1000);

            AiReportLog::logSuccess('global', $personnelId, [
                'sujet_id'   => $organismeId,
                'sujet_type' => 'organisme',
                'reponse_ia' => $aiAnalysis,
                'pdf_path'   => $pdfPath,
                'duree_ms'   => $duration,
            ]);

            return response()->json([
                'message'      => 'Rapport global généré avec succès',
                'pdf_url'      => url('storage/' . $pdfPath),
                'kpis'         => $kpis,
                'analyse_ia'   => $aiAnalysis,
                'duree_ms'     => $duration,
            ]);
        } catch (\Throwable $e) {
            AiReportLog::logError('global', $personnelId, $e->getMessage(), [
                'sujet_id'   => $organismeId,
                'sujet_type' => 'organisme',
            ]);
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/ai-reports/global/risk-profiles
     * Liste des bénéficiaires à risque élevé ou critique.
     *
     * Params :
     * - organisme_id (optionnel)
     * - date_debut / date_fin
     * - seuil_risque : 'eleve' | 'critique' (défaut: eleve)
     */
    public function riskProfiles(Request $request)
    {
        $request->validate([
            'organisme_id' => 'nullable|integer|exists:organisme_financements,id',
            'date_debut'   => 'nullable|date',
            'date_fin'     => 'nullable|date',
        ]);

        $organismeId = $request->input('organisme_id');
        $debut       = $request->input('date_debut', now()->subYear()->toDateString());
        $fin         = $request->input('date_fin', now()->toDateString());
        $seuil       = $request->input('seuil_risque', 'eleve');

        // Calculer la catégorisation
        $categorisation = $this->kpiService->payerCategorization($organismeId, $debut, $fin);

        // Filtrer selon le seuil
        $profiles = match($seuil) {
            'critique' => $categorisation['defaillants']['liste'],
            default    => array_merge(
                $categorisation['en_retard']['liste'],
                $categorisation['defaillants']['liste']
            ),
        };

        // Trier par taux croissant (pire en premier)
        usort($profiles, fn($a, $b) => $a['taux'] <=> $b['taux']);

        return response()->json([
            'seuil'               => $seuil,
            'periode'             => ['debut' => $debut, 'fin' => $fin],
            'organisme_id'        => $organismeId,
            'total_a_risque'      => count($profiles),
            'resume_categorisation' => [
                'bons_payeurs'   => $categorisation['bons_payeurs']['count'],
                'payeurs_moyens' => $categorisation['payeurs_moyens']['count'],
                'en_retard'      => $categorisation['en_retard']['count'],
                'defaillants'    => $categorisation['defaillants']['count'],
            ],
            'profils_a_risque'    => $profiles,
        ]);
    }

    /**
     * GET /api/ai-reports/global/history
     * Historique des rapports globaux générés.
     */
    public function history(Request $request)
    {
        $logs = AiReportLog::where('type_rapport', 'global')
            ->when($request->input('organisme_id'), fn($q, $v) => $q->where('sujet_id', $v)->where('sujet_type', 'organisme'))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($logs);
    }

    /**
     * GET /api/ai-reports/global/download/{logId}
     */
    public function download(int $logId)
    {
        $log = AiReportLog::where('id', $logId)->where('type_rapport', 'global')->firstOrFail();

        if (!$log->pdf_path || !Storage::disk('public')->exists($log->pdf_path)) {
            return response()->json(['message' => 'Fichier PDF non trouvé'], 404);
        }

        return Storage::disk('public')->download($log->pdf_path, basename($log->pdf_path));
    }
}
