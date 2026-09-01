<?php

namespace App\Services\Report;

/**
 * ChartService — Génération de graphiques SVG inline pour les rapports PDF.
 *
 * Utilise du SVG pur (vectoriel), rendu nativement par DomPDF.
 * Pas de dépendance Node.js ou librairie externe — léger et rapide.
 */
class ChartService
{
    private const COLORS = [
        '#4F46E5', // Indigo
        '#10B981', // Emerald
        '#F59E0B', // Amber
        '#EF4444', // Red
        '#8B5CF6', // Violet
        '#06B6D4', // Cyan
        '#F97316', // Orange
        '#84CC16', // Lime
    ];

    private const COLOR_SUCCESS = '#10B981';
    private const COLOR_WARNING = '#F59E0B';
    private const COLOR_DANGER  = '#EF4444';
    private const COLOR_INFO    = '#4F46E5';

    // =========================================================================
    // GRAPHIQUE BARRES — Remboursements prévu vs payé
    // =========================================================================

    /**
     * Génère un graphique en barres groupées (prévu vs payé).
     *
     * @param array $data [['label' => 'Jan', 'prevu' => 1000, 'paye' => 800], ...]
     */
    public function barChart(array $data, string $title = '', int $width = 520, int $height = 220): string
    {
        if (empty($data)) {
            return $this->emptyChart($width, $height, 'Aucune donnée disponible');
        }

        $padding     = ['top' => 40, 'right' => 20, 'bottom' => 50, 'left' => 55];
        $chartWidth  = $width - $padding['left'] - $padding['right'];
        $chartHeight = $height - $padding['top'] - $padding['bottom'];

        $maxValue = max(array_merge(
            array_column($data, 'prevu'),
            array_column($data, 'paye'),
            [1]
        ));

        $n         = count($data);
        $groupW    = $chartWidth / $n;
        $barW      = max(6, ($groupW * 0.35));
        $barGap    = 3;

        $ySteps  = 5;
        $yStep   = $this->niceStep($maxValue / $ySteps);
        $yMax    = $yStep * $ySteps;

        $svg = $this->svgOpen($width, $height);

        // Titre
        if ($title) {
            $svg .= $this->text($width / 2, 18, $title, ['font-size' => '11', 'font-weight' => 'bold', 'text-anchor' => 'middle', 'fill' => '#1E293B']);
        }

        // Fond du graphe
        $svg .= $this->rect($padding['left'], $padding['top'], $chartWidth, $chartHeight, '#F8FAFC', 1, '#E2E8F0');

        // Grille horizontale
        for ($i = 0; $i <= $ySteps; $i++) {
            $y   = $padding['top'] + $chartHeight - ($i / $ySteps) * $chartHeight;
            $val = $i * $yStep;
            $svg .= $this->line($padding['left'], $y, $padding['left'] + $chartWidth, $y, '#CBD5E1', $i === 0 ? 1.5 : 0.5);
            $svg .= $this->text($padding['left'] - 6, $y + 4, $this->formatAmount($val), ['font-size' => '8', 'text-anchor' => 'end', 'fill' => '#64748B']);
        }

        // Barres
        foreach ($data as $i => $item) {
            $x     = $padding['left'] + $i * $groupW + $groupW / 2;
            $prevH = $yMax > 0 ? (($item['prevu'] ?? 0) / $yMax) * $chartHeight : 0;
            $payH  = $yMax > 0 ? (($item['paye']  ?? 0) / $yMax) * $chartHeight : 0;

            // Barre prévu (bleu)
            $xPrev = $x - $barW - $barGap / 2;
            $yPrev = $padding['top'] + $chartHeight - $prevH;
            $svg  .= $this->rect($xPrev, $yPrev, $barW, $prevH, self::COLOR_INFO, 0, '', 3);

            // Barre payé (vert)
            $xPay = $x + $barGap / 2;
            $yPay = $padding['top'] + $chartHeight - $payH;
            $svg .= $this->rect($xPay, $yPay, $barW, $payH, self::COLOR_SUCCESS, 0, '', 3);

            // Label mois
            $svg .= $this->text($x, $padding['top'] + $chartHeight + 14, $item['label'] ?? '', ['font-size' => '8', 'text-anchor' => 'middle', 'fill' => '#475569']);
        }

        // Légende
        $legendY = $height - 12;
        $svg .= $this->rect($padding['left'], $legendY - 8, 10, 8, self::COLOR_INFO);
        $svg .= $this->text($padding['left'] + 13, $legendY, 'Prévu', ['font-size' => '8', 'fill' => '#475569']);
        $svg .= $this->rect($padding['left'] + 55, $legendY - 8, 10, 8, self::COLOR_SUCCESS);
        $svg .= $this->text($padding['left'] + 68, $legendY, 'Payé', ['font-size' => '8', 'fill' => '#475569']);

        $svg .= '</svg>';
        return $svg;
    }

    // =========================================================================
    // GRAPHIQUE DONUT — Catégorisation des payeurs
    // =========================================================================

    /**
     * Génère un donut chart pour la répartition des payeurs.
     *
     * @param array $segments [['label' => 'Bons payeurs', 'value' => 45, 'color' => '#10B981'], ...]
     */
    public function donutChart(array $segments, string $title = '', int $size = 200): string
    {
        if (empty($segments)) {
            return $this->emptyChart($size, $size, 'Aucune donnée');
        }

        $total = array_sum(array_column($segments, 'value'));
        if ($total === 0) {
            return $this->emptyChart($size, $size, 'Aucune donnée');
        }

        $cx       = $size / 2;
        $cy       = $size / 2;
        $r        = ($size / 2) * 0.65;
        $innerR   = $r * 0.55;
        $startAngle = -90; // Commencer en haut

        $svg = $this->svgOpen($size, $size + 60);

        // Titre
        if ($title) {
            $svg .= $this->text($cx, 14, $title, ['font-size' => '10', 'font-weight' => 'bold', 'text-anchor' => 'middle', 'fill' => '#1E293B']);
        }

        // Tranches du donut
        $angle = $startAngle;
        foreach ($segments as $idx => $seg) {
            $slice = ($seg['value'] / $total) * 360;
            $color = $seg['color'] ?? (self::COLORS[$idx % count(self::COLORS)]);
            $svg  .= $this->donutSlice($cx, $cy + 10, $r, $innerR, $angle, $angle + $slice, $color);
            $angle += $slice;
        }

        // Label central — total
        $svg .= $this->text($cx, $cy + 7, $total, ['font-size' => '16', 'font-weight' => 'bold', 'text-anchor' => 'middle', 'fill' => '#1E293B']);
        $svg .= $this->text($cx, $cy + 18, 'total', ['font-size' => '8', 'text-anchor' => 'middle', 'fill' => '#64748B']);

        // Légende
        $legendY = $size + 15;
        $lx = 10;
        foreach ($segments as $idx => $seg) {
            $color  = $seg['color'] ?? (self::COLORS[$idx % count(self::COLORS)]);
            $pct    = round($seg['value'] / $total * 100, 1);
            $label  = ($seg['label'] ?? '') . " ($pct%)";

            if ($idx % 2 === 0 && $idx > 0) {
                $legendY += 14;
                $lx = 10;
            }

            $svg .= $this->rect($lx, $legendY - 7, 8, 8, $color);
            $svg .= $this->text($lx + 11, $legendY, $label, ['font-size' => '7.5', 'fill' => '#475569']);
            $lx += $size / 2;
        }

        $svg .= '</svg>';
        return $svg;
    }

    // =========================================================================
    // GRAPHIQUE LIGNE — Évolution temporelle
    // =========================================================================

    /**
     * Génère un graphique en courbe pour l'évolution du taux de remboursement.
     *
     * @param array $data [['label' => 'Jan 2025', 'value' => 87.5], ...]
     */
    public function lineChart(array $data, string $title = '', int $width = 520, int $height = 180): string
    {
        if (count($data) < 2) {
            return $this->emptyChart($width, $height, 'Données insuffisantes');
        }

        $padding     = ['top' => 30, 'right' => 20, 'bottom' => 40, 'left' => 45];
        $chartWidth  = $width - $padding['left'] - $padding['right'];
        $chartHeight = $height - $padding['top'] - $padding['bottom'];

        $values  = array_column($data, 'value');
        $maxVal  = max(max($values), 1);
        $minVal  = max(0, min($values) - 5);
        $range   = $maxVal - $minVal ?: 1;

        $n      = count($data);
        $stepX  = $chartWidth / max($n - 1, 1);

        $svg = $this->svgOpen($width, $height);

        // Titre
        if ($title) {
            $svg .= $this->text($width / 2, 16, $title, ['font-size' => '10', 'font-weight' => 'bold', 'text-anchor' => 'middle', 'fill' => '#1E293B']);
        }

        // Fond
        $svg .= $this->rect($padding['left'], $padding['top'], $chartWidth, $chartHeight, '#F8FAFC', 1, '#E2E8F0');

        // Grille + labels Y
        for ($i = 0; $i <= 4; $i++) {
            $val = $minVal + ($i / 4) * $range;
            $y   = $padding['top'] + $chartHeight - ($i / 4) * $chartHeight;
            $svg .= $this->line($padding['left'], $y, $padding['left'] + $chartWidth, $y, '#CBD5E1', 0.5);
            $svg .= $this->text($padding['left'] - 5, $y + 3, round($val, 1) . '%', ['font-size' => '7', 'text-anchor' => 'end', 'fill' => '#64748B']);
        }

        // Points + ligne
        $points = [];
        foreach ($data as $i => $item) {
            $x        = $padding['left'] + $i * $stepX;
            $y        = $padding['top'] + $chartHeight - (($item['value'] - $minVal) / $range) * $chartHeight;
            $points[] = "$x,$y";
        }

        // Aire sous la courbe
        $areaPoints = $points[0];
        foreach (array_slice($points, 1) as $p) {
            $areaPoints .= " $p";
        }
        $lastX  = $padding['left'] + ($n - 1) * $stepX;
        $baseY  = $padding['top'] + $chartHeight;
        $svg   .= "<polygon points=\"$padding[left],$baseY $areaPoints $lastX,$baseY\" fill=\"" . self::COLOR_INFO . "\" fill-opacity=\"0.1\"/>";

        // Ligne principale
        $svg .= "<polyline points=\"" . implode(' ', $points) . "\" fill=\"none\" stroke=\"" . self::COLOR_INFO . "\" stroke-width=\"2\" stroke-linejoin=\"round\"/>";

        // Points individuels + labels X
        foreach ($data as $i => $item) {
            $x   = $padding['left'] + $i * $stepX;
            $y   = $padding['top'] + $chartHeight - (($item['value'] - $minVal) / $range) * $chartHeight;
            $svg .= "<circle cx=\"$x\" cy=\"$y\" r=\"3\" fill=\"white\" stroke=\"" . self::COLOR_INFO . "\" stroke-width=\"2\"/>";

            if ($i % max(1, intdiv($n, 6)) === 0 || $i === $n - 1) {
                $svg .= $this->text($x, $padding['top'] + $chartHeight + 12, $item['label'] ?? '', ['font-size' => '7', 'text-anchor' => 'middle', 'fill' => '#64748B']);
            }
        }

        $svg .= '</svg>';
        return $svg;
    }

    // =========================================================================
    // GAUGE — Score de risque individuel
    // =========================================================================

    /**
     * Jauge semi-circulaire pour afficher un score de risque (0-100).
     */
    public function gaugeChart(int $score, string $label = 'Score de risque', int $size = 180): string
    {
        $cx     = $size / 2;
        $cy     = $size * 0.6;
        $r      = $size * 0.38;
        $score  = max(0, min(100, $score));

        // Couleur selon le score
        $color = match(true) {
            $score <= 20 => self::COLOR_SUCCESS,
            $score <= 50 => self::COLOR_WARNING,
            default      => self::COLOR_DANGER,
        };

        $riskLabel = match(true) {
            $score <= 20 => 'Faible',
            $score <= 50 => 'Modéré',
            $score <= 75 => 'Élevé',
            default      => 'Critique',
        };

        // Arc de fond (gris)
        $bgArc = $this->describeArc($cx, $cy, $r, 180, 360);
        // Arc coloré (selon score)
        $endAngle = 180 + ($score / 100) * 180;
        $scoreArc = $this->describeArc($cx, $cy, $r, 180, $endAngle);

        $svg  = $this->svgOpen($size, $size * 0.75);
        $svg .= "<path d=\"$bgArc\" fill=\"none\" stroke=\"#E2E8F0\" stroke-width=\"14\" stroke-linecap=\"round\"/>";
        $svg .= "<path d=\"$scoreArc\" fill=\"none\" stroke=\"$color\" stroke-width=\"14\" stroke-linecap=\"round\"/>";
        $svg .= $this->text($cx, $cy - 8, "$score", ['font-size' => '22', 'font-weight' => 'bold', 'text-anchor' => 'middle', 'fill' => $color]);
        $svg .= $this->text($cx, $cy + 8, $riskLabel, ['font-size' => '10', 'text-anchor' => 'middle', 'fill' => '#64748B']);
        $svg .= $this->text($cx, $cy + 22, $label, ['font-size' => '8', 'text-anchor' => 'middle', 'fill' => '#94A3B8']);
        $svg .= '</svg>';

        return $svg;
    }

    // =========================================================================
    // HELPERS SVG
    // =========================================================================

    private function svgOpen(int $width, int $height): string
    {
        return "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"$width\" height=\"$height\" viewBox=\"0 0 $width $height\" font-family=\"DejaVu Sans, Arial, sans-serif\">";
    }

    private function rect(float $x, float $y, float $w, float $h, string $fill, float $strokeW = 0, string $stroke = '', int $rx = 0): string
    {
        $stroke = $stroke ? "stroke=\"$stroke\" stroke-width=\"$strokeW\"" : '';
        return "<rect x=\"$x\" y=\"$y\" width=\"$w\" height=\"$h\" fill=\"$fill\" rx=\"$rx\" $stroke/>";
    }

    private function text(float $x, float $y, $value, array $attrs = []): string
    {
        $style = '';
        foreach ($attrs as $k => $v) {
            $style .= "$k=\"$v\" ";
        }
        return "<text x=\"$x\" y=\"$y\" $style>" . htmlspecialchars((string)$value) . "</text>";
    }

    private function line(float $x1, float $y1, float $x2, float $y2, string $color = '#CBD5E1', float $sw = 1): string
    {
        return "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" stroke=\"$color\" stroke-width=\"$sw\"/>";
    }

    private function donutSlice(float $cx, float $cy, float $r, float $ir, float $startDeg, float $endDeg, string $color): string
    {
        if (abs($endDeg - $startDeg) >= 360) {
            $endDeg -= 0.01;
        }

        $start = $this->polarToCartesian($cx, $cy, $r, $endDeg);
        $end   = $this->polarToCartesian($cx, $cy, $r, $startDeg);
        $iStart = $this->polarToCartesian($cx, $cy, $ir, $endDeg);
        $iEnd   = $this->polarToCartesian($cx, $cy, $ir, $startDeg);
        $large  = abs($endDeg - $startDeg) > 180 ? 1 : 0;

        $d = "M {$start['x']} {$start['y']} A $r $r 0 $large 0 {$end['x']} {$end['y']} L {$iEnd['x']} {$iEnd['y']} A $ir $ir 0 $large 1 {$iStart['x']} {$iStart['y']} Z";
        return "<path d=\"$d\" fill=\"$color\" stroke=\"white\" stroke-width=\"2\"/>";
    }

    private function polarToCartesian(float $cx, float $cy, float $r, float $angleDeg): array
    {
        $rad = ($angleDeg - 90) * M_PI / 180;
        return ['x' => round($cx + $r * cos($rad), 4), 'y' => round($cy + $r * sin($rad), 4)];
    }

    private function describeArc(float $cx, float $cy, float $r, float $startDeg, float $endDeg): string
    {
        $start = $this->polarToCartesian($cx, $cy, $r, $endDeg);
        $end   = $this->polarToCartesian($cx, $cy, $r, $startDeg);
        $large = abs($endDeg - $startDeg) > 180 ? 1 : 0;
        return "M {$start['x']} {$start['y']} A $r $r 0 $large 0 {$end['x']} {$end['y']}";
    }

    private function emptyChart(int $w, int $h, string $msg = 'Pas de données'): string
    {
        $svg = $this->svgOpen($w, $h);
        $svg .= $this->rect(0, 0, $w, $h, '#F8FAFC', 1, '#E2E8F0', 4);
        $svg .= $this->text($w / 2, $h / 2, $msg, ['font-size' => '10', 'text-anchor' => 'middle', 'fill' => '#94A3B8']);
        $svg .= '</svg>';
        return $svg;
    }

    private function niceStep(float $roughStep): float
    {
        $magnitude = pow(10, floor(log10($roughStep)));
        $residual  = $roughStep / $magnitude;
        return ($residual < 1.5 ? 1 : ($residual < 3 ? 2 : ($residual < 7 ? 5 : 10))) * $magnitude;
    }

    private function formatAmount(float $val): string
    {
        if ($val >= 1_000_000) return round($val / 1_000_000, 1) . 'M';
        if ($val >= 1_000)     return round($val / 1_000, 1) . 'K';
        return (string) round($val);
    }
}
