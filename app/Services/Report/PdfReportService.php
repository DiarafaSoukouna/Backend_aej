<?php

namespace App\Services\Report;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\PeriodicBulletin;

/**
 * PdfReportService — Génération de PDFs via DomPDF + templates Blade.
 * Les graphiques SVG sont injectés directement dans le HTML.
 */
class PdfReportService
{
    public function __construct(
        private ChartService $chartService
    ) {}

    // =========================================================================
    // PDF INDIVIDUEL
    // =========================================================================

    /**
     * Génère le PDF du rapport individuel d'un jeune.
     *
     * @param array $profile     Données du promoteur
     * @param array $kpis        KPIs calculés par KpiCalculatorService
     * @param string $aiComment  Commentaire narratif généré par l'IA
     * @return string Chemin relatif du fichier PDF dans storage
     */
    public function generateIndividualReport(array $profile, array $kpis, string $aiComment): string
    {
        // Générer les graphiques SVG
        $charts = $this->buildIndividualCharts($kpis);

        $data = [
            'profile'    => $profile,
            'kpis'       => $kpis,
            'aiComment'  => $aiComment,
            'charts'     => $charts,
            'generatedAt' => now()->format('d/m/Y à H:i'),
        ];

        $pdf = Pdf::loadView('reports.individual', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 150,
            ]);

        $filename = 'reports/individual/rapport_' . ($profile['matricule'] ?? $profile['id'] ?? 'inconnu') . '_' . now()->format('Ymd_His') . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    // =========================================================================
    // PDF GLOBAL
    // =========================================================================

    /**
     * Génère le PDF du rapport global.
     */
    public function generateGlobalReport(array $kpis, string $aiAnalysis, ?string $organismeNom = null): string
    {
        $charts = $this->buildGlobalCharts($kpis);

        $data = [
            'kpis'          => $kpis,
            'aiAnalysis'    => $aiAnalysis,
            'organismeNom'  => $organismeNom,
            'charts'        => $charts,
            'generatedAt'   => now()->format('d/m/Y à H:i'),
        ];

        $pdf = Pdf::loadView('reports.global', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 150,
            ]);

        $suffix   = $organismeNom ? '_' . \Str::slug($organismeNom) : '_global';
        $filename = 'reports/global/rapport' . $suffix . '_' . now()->format('Ymd_His') . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    // =========================================================================
    // PDF BULLETIN PÉRIODIQUE
    // =========================================================================

    /**
     * Génère le PDF d'un bulletin périodique.
     */
    public function generateBulletin(PeriodicBulletin $bulletin): string
    {
        $charts = $this->buildBulletinCharts($bulletin);

        $data = [
            'bulletin'   => $bulletin,
            'kpis'       => $bulletin->kpis,
            'charts'     => $charts,
            'generatedAt' => now()->format('d/m/Y à H:i'),
        ];

        $pdf = Pdf::loadView('reports.bulletin', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 150,
            ]);

        $typeLabel = $bulletin->periode_type;
        $filename  = 'reports/bulletins/bulletin_' . $typeLabel . '_' . $bulletin->periode_debut->format('Ym') . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    // =========================================================================
    // CONSTRUCTEURS DE GRAPHIQUES
    // =========================================================================

    private function buildIndividualCharts(array $kpis): array
    {
        // Histogramme remboursements (si données mensuelles disponibles)
        $barData = [];
        if (!empty($kpis['evolution_mensuelle'])) {
            foreach ($kpis['evolution_mensuelle'] as $m) {
                $barData[] = [
                    'label' => $m['mois'] ?? '',
                    'prevu' => (float) ($m['prevu'] ?? 0),
                    'paye'  => (float) ($m['paye'] ?? 0),
                ];
            }
        }

        return [
            'remboursements' => $this->chartService->barChart($barData, 'Remboursements — Prévu vs Payé', 480, 180),
            'risque'         => $this->chartService->gaugeChart((int)($kpis['score_risque'] ?? 0), 'Score de risque', 160),
        ];
    }

    private function buildGlobalCharts(array $kpis): array
    {
        // Évolution mensuelle
        $lineData = [];
        if (!empty($kpis['evolution_mensuelle'])) {
            foreach ($kpis['evolution_mensuelle'] as $m) {
                $prevu  = (float)($m->prevu ?? $m['prevu'] ?? 0);
                $paye   = (float)($m->paye  ?? $m['paye']  ?? 0);
                $taux   = $prevu > 0 ? round($paye / $prevu * 100, 1) : 0;
                $lineData[] = ['label' => $m->mois ?? $m['mois'] ?? '', 'value' => $taux];
            }
        }

        // Donut catégorisation
        $cat = $kpis['categorisation'] ?? [];
        $donutData = [
            ['label' => 'Bons payeurs',    'value' => $cat['bons_payeurs']['count']   ?? 0, 'color' => '#10B981'],
            ['label' => 'Payeurs moyens',  'value' => $cat['payeurs_moyens']['count'] ?? 0, 'color' => '#F59E0B'],
            ['label' => 'En retard',       'value' => $cat['en_retard']['count']      ?? 0, 'color' => '#F97316'],
            ['label' => 'Défaillants',     'value' => $cat['defaillants']['count']    ?? 0, 'color' => '#EF4444'],
        ];
        $donutData = array_filter($donutData, fn($d) => $d['value'] > 0);

        // Barres par secteur
        $barData = [];
        if (!empty($kpis['par_secteur'])) {
            foreach (array_slice((array)$kpis['par_secteur'], 0, 8) as $s) {
                $s = (array) $s;
                $barData[] = [
                    'label' => substr($s['secteur'] ?? '', 0, 8),
                    'prevu' => (float)($s['nombre_projets'] ?? 0),
                    'paye'  => 0,
                ];
            }
        }

        return [
            'evolution'     => $this->chartService->lineChart($lineData, 'Évolution du taux de remboursement (%)', 480, 160),
            'categorisation' => $this->chartService->donutChart(array_values($donutData), 'Répartition des payeurs', 200),
            'par_secteur'   => $this->chartService->barChart($barData, 'Projets par secteur', 480, 160),
        ];
    }

    private function buildBulletinCharts(PeriodicBulletin $bulletin): array
    {
        return $this->buildGlobalCharts($bulletin->kpis ?? []);
    }
}
