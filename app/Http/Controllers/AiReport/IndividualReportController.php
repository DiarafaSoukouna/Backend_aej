<?php

namespace App\Http\Controllers\AiReport;

use App\Http\Controllers\Controller;
use App\Models\AiReportLog;
use App\Models\PeriodicBulletin;
use App\Services\Ai\AiReportService;
use App\Services\Ai\SqlGuardService;
use App\Services\Report\KpiCalculatorService;
use App\Services\Report\PdfReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Module 1 — Rapports individuels par jeune bénéficiaire.
 */
class IndividualReportController extends Controller
{
    public function __construct(
        private AiReportService     $aiService,
        private SqlGuardService     $sqlGuard,
        private KpiCalculatorService $kpiService,
        private PdfReportService    $pdfService,
    ) {}

    /**
     * POST /api/ai-reports/individual/{promoteurId}/pdf
     * Génère le PDF complet pour un jeune bénéficiaire.
     */
    public function generatePdf(Request $request, int $promoteurId)
    {
        set_time_limit(180); // 3 minutes max pour la génération IA + PDF
        $start       = microtime(true);
        $personnelId = auth()->id();

        try {
            // 1. Charger le profil
            $promoteur = DB::table('promoteurs')->where('id', $promoteurId)->first();
            if (!$promoteur) {
                return response()->json(['message' => 'Promoteur non trouvé'], 404);
            }

            // 2. Calculer les KPIs (déterministe)
            $kpis = $this->kpiService->individualKpis($promoteurId);

            // 3. Générer le commentaire IA
            $aiComment = $this->aiService->generateIndividualComment((array) $promoteur, $kpis);

            // 4. Générer le PDF
            $pdfPath = $this->pdfService->generateIndividualReport((array) $promoteur, $kpis, $aiComment);

            // 5. Journaliser
            $duration = (int) round((microtime(true) - $start) * 1000);
            AiReportLog::logSuccess('individual', $personnelId, [
                'sujet_id'   => $promoteurId,
                'sujet_type' => 'promoteur',
                'reponse_ia' => $aiComment,
                'pdf_path'   => $pdfPath,
                'duree_ms'   => $duration,
            ]);

            return response()->json([
                'message'   => 'Rapport individuel généré avec succès',
                'pdf_url'   => url('storage/' . $pdfPath),
                'kpis'      => $kpis,
                'duree_ms'  => $duration,
            ]);
        } catch (\Throwable $e) {
            AiReportLog::logError('individual', $personnelId, $e->getMessage(), [
                'sujet_id'   => $promoteurId,
                'sujet_type' => 'promoteur',
            ]);
            return response()->json(['message' => 'Erreur lors de la génération : ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/ai-reports/individual/{promoteurId}/ask
     * Question analytique ponctuelle avec text-to-SQL sécurisé.
     */
    public function askQuestion(Request $request, int $promoteurId)
    {
        set_time_limit(120); // 2 minutes max pour l'appel IA
        $request->validate([
            'question' => 'required|string|max:500',
        ]);

        $start       = microtime(true);
        $personnelId = auth()->id();
        $question    = $request->input('question');

        $promoteur = DB::table('promoteurs')->where('id', $promoteurId)->first();
        if (!$promoteur) {
            return response()->json(['message' => 'Promoteur non trouvé'], 404);
        }

        try {
            // Schéma textuel (PAS les credentials — uniquement la structure)
            $schema = $this->getSchemaDescription();

            // Demander à l'IA de générer le SQL
            $sqlBrut = $this->aiService->generateSql(
                $schema,
                "Pour le promoteur avec l'ID $promoteurId (matricule: {$promoteur->matriculeaej}) : $question",
                $this->sqlGuard->getAllowedTablesDescription()
            );

            // Vérifier si l'IA a signalé une question impossible
            if (str_starts_with(strtoupper(trim($sqlBrut)), 'IMPOSSIBLE')) {
                AiReportLog::logSuccess('question', $personnelId, [
                    'sujet_id'    => $promoteurId,
                    'sujet_type'  => 'promoteur',
                    'question'    => $question,
                    'sql_genere'  => $sqlBrut,
                    'sql_valide'  => false,
                    'statut'      => 'sql_rejected',
                ]);
                return response()->json([
                    'message'    => 'Cette question ne peut pas être répondue via les données disponibles.',
                    'reponse_ia' => $sqlBrut,
                    'sql_valide' => false,
                ]);
            }

            // Valider + exécuter le SQL
            $execution = $this->sqlGuard->executeIfValid($sqlBrut);

            $duration = (int) round((microtime(true) - $start) * 1000);

            AiReportLog::logSuccess('question', $personnelId, [
                'sujet_id'          => $promoteurId,
                'sujet_type'        => 'promoteur',
                'question'          => $question,
                'sql_genere'        => $sqlBrut,
                'sql_valide'        => $execution['valid'],
                'erreurs_validation' => $execution['errors'],
                'duree_ms'          => $duration,
                'statut'            => $execution['valid'] ? 'success' : 'sql_rejected',
            ]);

            if (!$execution['valid']) {
                return response()->json([
                    'message'    => 'La requête SQL générée est invalide (bloquée pour sécurité).',
                    'erreurs'    => $execution['errors'],
                    'sql_genere' => $sqlBrut,
                    'sql_valide' => false,
                ], 422);
            }

            return response()->json([
                'question'   => $question,
                'sql_genere' => $sqlBrut,
                'sql_valide' => true,
                'resultats'  => $execution['results'],
                'nb_lignes'  => $execution['row_count'],
                'duree_ms'   => $duration,
            ]);
        } catch (\Throwable $e) {
            AiReportLog::logError('question', $personnelId, $e->getMessage(), [
                'sujet_id'   => $promoteurId,
                'sujet_type' => 'promoteur',
                'question'   => $question,
            ]);
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/ai-reports/individual/{promoteurId}/history
     * Historique des rapports et questions générés pour un jeune.
     */
    public function history(int $promoteurId)
    {
        $logs = AiReportLog::where('sujet_id', $promoteurId)
            ->where('sujet_type', 'promoteur')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($logs);
    }

    /**
     * GET /api/ai-reports/individual/{promoteurId}/download/{logId}
     * Télécharge un PDF précédemment généré.
     */
    public function download(int $promoteurId, int $logId)
    {
        $log = AiReportLog::where('id', $logId)
            ->where('sujet_id', $promoteurId)
            ->where('sujet_type', 'promoteur')
            ->firstOrFail();

        if (!$log->pdf_path || !Storage::disk('public')->exists($log->pdf_path)) {
            return response()->json(['message' => 'Fichier PDF non trouvé'], 404);
        }

        return Storage::disk('public')->download($log->pdf_path, basename($log->pdf_path));
    }

    /**
     * Retourne la description textuelle du schéma (sans credentials).
     */
    private function getSchemaDescription(): string
    {
        return <<<SCHEMA
Tables disponibles et leurs colonnes principales :

promoteurs: id, nom, prenom, matriculeaej, email, telephone, sexe_id, lieuhabitation_id, statut, created_at
micro_projets: id, code, intitule, promoteur_id, organisme_id, secteur_id, montant_total, statut, stade_projet, type_projet, date_certification, created_at
budgets: id, micro_projet_id, intitule, montant_accorde, date_accord, source, statut, devise, deblocage, date_deblocage
plan_remboursements: id, micro_projet_id, budget_id, echeance_mensuelle, montant_echeance, periode, capital_rembourse, capital_restant, interets
remboursements: id, plan_remboursement_id, promoteur_id, montant_echu, montant_paye, montant_impaye, penalites, date_paiement, statut
recouvrements: id, micro_projet_id, plan_remboursement_id, montant, date_recouvrement, mode_recouvrement, statut
decaissements: id, budget_id, micro_projet_id, montant, date_decaissement, statut
exploitations: id, micro_projet_id, date_visite, statut_projet, nbre_emplois, chiffre_affaires
organisme_financements: id, nom, sigle, type, region_id
secteurs: id, code, libelle
SCHEMA;
    }
}
