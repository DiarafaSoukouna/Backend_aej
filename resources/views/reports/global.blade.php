<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Rapport Global — {{ $organismeNom ?? 'Transversal' }}</title>
  @include('reports.partials.header')
  <style>
    .progress-bar-wrap { background: #E2E8F0; border-radius: 6px; height: 8px; width: 100%; margin-top: 4px; }
    .progress-bar-fill { height: 8px; border-radius: 6px; background: linear-gradient(90deg, #10B981, #34D399); }
    .progress-bar-fill.warning { background: linear-gradient(90deg, #F59E0B, #FCD34D); }
    .progress-bar-fill.danger  { background: linear-gradient(90deg, #EF4444, #FCA5A5); }
    .summary-intro {
      background: linear-gradient(135deg, #F0F9FF, #EFF6FF);
      border: 1px solid #BFDBFE;
      border-radius: 8px;
      padding: 13px 16px;
      font-size: 9px;
      line-height: 1.75;
      color: #1E293B;
      margin-bottom: 18px;
    }
    .summary-intro strong { color: #0F2447; }
  </style>
</head>
<body>

{{-- ═══ HEADER ═══ --}}
<div class="pdf-header">
  <div class="org-name">Agence Emploi Jeunes (AEJ) · République de Côte d'Ivoire</div>
  <h1>Rapport Global {{ $organismeNom ? '— ' . $organismeNom : 'Transversal' }}</h1>
  <div class="subtitle">
    Vue d'ensemble du portefeuille · Tendances · Profils à risque · Recommandations stratégiques
  </div>
  <span class="header-badge">
    Période : {{ \Carbon\Carbon::parse($kpis['periode']['debut'] ?? now())->format('d/m/Y') }}
    au {{ \Carbon\Carbon::parse($kpis['periode']['fin'] ?? now())->format('d/m/Y') }}
  </span>
</div>

<div class="meta-bar">
  <span>Périmètre : <strong>{{ $organismeNom ?? 'Tous organismes confondus' }}</strong></span>
  <span>Généré le : <strong>{{ $generatedAt }}</strong></span>
  <span>Usage interne — Confidentiel AEJ</span>
</div>

<div class="content">

  {{-- ── Résumé exécutif ── --}}
  @if(!empty($aiAnalysis))
  <div class="summary-intro">
    <strong>Résumé exécutif —</strong>
    {{ Str::limit(strip_tags($aiAnalysis), 380) }}
  </div>
  @endif

  {{-- ── Vue d'ensemble ── --}}
  <div class="section">
    <div class="section-title">Vue d'ensemble du portefeuille</div>
    <table style="width:100%; border-collapse:separate; border-spacing:6px 0;">
      <tr>
        <td style="width:25%;">
          <div class="kpi-card info">
            <span class="kpi-value">{{ number_format($kpis['total_promoteurs'] ?? 0) }}</span>
            <span class="kpi-label">Bénéficiaires actifs</span>
          </div>
        </td>
        <td style="width:25%;">
          <div class="kpi-card">
            <span class="kpi-value">{{ number_format($kpis['total_projets'] ?? 0) }}</span>
            <span class="kpi-label">Projets financés</span>
          </div>
        </td>
        <td style="width:25%;">
          <div class="kpi-card">
            <span class="kpi-value" style="font-size:12px;">{{ number_format($kpis['budget_total_accorde'] ?? 0, 0, ',', ' ') }}</span>
            <span class="kpi-label">Budget total accordé (FCFA)</span>
          </div>
        </td>
        <td style="width:25%;">
          @php $taux = $kpis['taux_remboursement'] ?? 0; @endphp
          <div class="kpi-card {{ $taux >= 80 ? 'success' : ($taux >= 50 ? 'warning' : 'danger') }}">
            <span class="kpi-value">{{ $taux }}%</span>
            <span class="kpi-label">Taux de remboursement global</span>
          </div>
        </td>
      </tr>
    </table>
  </div>

  {{-- ── Flux financiers ── --}}
  <div class="section">
    <div class="section-title">Flux financiers</div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Indicateur</th>
          <th style="text-align:right;">Montant (FCFA)</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Montant total attendu (échéances cumulées)</td>
          <td style="text-align:right;"><strong>{{ number_format($kpis['montant_prevu'] ?? 0, 0, ',', ' ') }}</strong></td>
          <td style="color:#64748B;">Planifié</td>
        </tr>
        <tr>
          <td>Montant effectivement recouvré</td>
          <td style="text-align:right; color:#059669;"><strong>{{ number_format($kpis['montant_paye'] ?? 0, 0, ',', ' ') }}</strong></td>
          <td style="color:#059669;">✓ Confirmé</td>
        </tr>
        <tr>
          <td>Montant impayé résiduel</td>
          <td style="text-align:right; color:#DC2626;"><strong>{{ number_format($kpis['montant_impaye'] ?? 0, 0, ',', ' ') }}</strong></td>
          <td style="color:#DC2626;">⚠ À recouvrer</td>
        </tr>
      </tbody>
    </table>

    {{-- Barre de progression ── --}}
    @php
      $taux = min(100, max(0, $kpis['taux_remboursement'] ?? 0));
      $barClass = $taux >= 80 ? '' : ($taux >= 50 ? 'warning' : 'danger');
    @endphp
    <div style="margin-top:10px;">
      <div class="progress-bar-wrap">
        <div class="progress-bar-fill {{ $barClass }}" style="width:{{ $taux }}%;"></div>
      </div>
      <div style="font-size:8px; color:#64748B; margin-top:4px; text-align:right;">
        Taux de recouvrement : <strong style="color: {{ $taux >= 80 ? '#059669' : ($taux >= 50 ? '#D97706' : '#DC2626') }};">{{ $taux }}%</strong>
      </div>
    </div>
  </div>

  {{-- ── Graphiques ── --}}
  <div class="section">
    <div class="section-title">Analyses graphiques</div>
    <table class="two-col">
      <tr>
        @if(!empty($charts['evolution']))
        <td>
          <div class="chart-box">{!! $charts['evolution'] !!}</div>
        </td>
        @endif
        @if(!empty($charts['categorisation']))
        <td>
          <div class="chart-box">{!! $charts['categorisation'] !!}</div>
        </td>
        @endif
      </tr>
    </table>
    @if(!empty($charts['par_secteur']))
    <div class="chart-box" style="margin-top:0;">{!! $charts['par_secteur'] !!}</div>
    @endif
  </div>

  {{-- ── Catégorisation ── --}}
  @php $cat = $kpis['categorisation'] ?? []; @endphp
  @if(!empty($cat))
  <div class="section">
    <div class="section-title">Catégorisation des bénéficiaires par comportement de paiement</div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Catégorie</th>
          <th style="text-align:center;">Effectif</th>
          <th style="text-align:center;">Part du portefeuille</th>
          <th>Critère</th>
          <th>Recommandation</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><span class="badge-success">Bons payeurs</span></td>
          <td style="text-align:center;"><strong>{{ $cat['bons_payeurs']['count'] ?? 0 }}</strong></td>
          <td style="text-align:center;">{{ $cat['bons_payeurs']['taux_pct'] ?? 0 }}%</td>
          <td>≥ 95%</td>
          <td style="color:#059669; font-size:8px;">Maintenir l'accompagnement</td>
        </tr>
        <tr>
          <td><span class="badge-info">Payeurs réguliers</span></td>
          <td style="text-align:center;"><strong>{{ $cat['payeurs_moyens']['count'] ?? 0 }}</strong></td>
          <td style="text-align:center;">{{ $cat['payeurs_moyens']['taux_pct'] ?? 0 }}%</td>
          <td>70% — 94%</td>
          <td style="color:#0284C7; font-size:8px;">Suivi standard</td>
        </tr>
        <tr>
          <td><span class="badge-warning">En situation de retard</span></td>
          <td style="text-align:center;"><strong>{{ $cat['en_retard']['count'] ?? 0 }}</strong></td>
          <td style="text-align:center;">{{ $cat['en_retard']['taux_pct'] ?? 0 }}%</td>
          <td>30% — 69%</td>
          <td style="color:#D97706; font-size:8px;">Relance et suivi rapproché</td>
        </tr>
        <tr>
          <td><span class="badge-danger">Défaillants</span></td>
          <td style="text-align:center;"><strong>{{ $cat['defaillants']['count'] ?? 0 }}</strong></td>
          <td style="text-align:center;">{{ $cat['defaillants']['taux_pct'] ?? 0 }}%</td>
          <td>< 30%</td>
          <td style="color:#DC2626; font-size:8px;">Procédure de recouvrement urgente</td>
        </tr>
      </tbody>
    </table>
  </div>
  @endif

  {{-- ── Par secteur ── --}}
  @if(!empty($kpis['par_secteur']))
  <div class="section">
    <div class="section-title">Répartition par secteur d'activité (Top 8)</div>
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Secteur</th>
          <th style="text-align:center;">Projets</th>
          <th style="text-align:right;">Montant total (FCFA)</th>
        </tr>
      </thead>
      <tbody>
        @foreach(array_slice((array)$kpis['par_secteur'], 0, 8) as $idx => $s)
        @php $s = (array)$s; @endphp
        <tr>
          <td style="color:#94A3B8; font-weight:bold;">{{ $idx + 1 }}</td>
          <td>{{ $s['secteur'] ?? 'N/A' }}</td>
          <td style="text-align:center;"><strong>{{ $s['nombre_projets'] ?? 0 }}</strong></td>
          <td style="text-align:right;">{{ number_format($s['montant_total'] ?? 0, 0, ',', ' ') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  {{-- ── Analyse & Recommandations ── --}}
  @if(!empty($aiAnalysis))
  <div class="section">
    <div class="section-title">Analyse stratégique & Recommandations</div>
    <div class="analysis-block">
      <span class="analysis-label">
        Analyse — {{ $organismeNom ?? 'Portefeuille global' }} ·
        Période {{ \Carbon\Carbon::parse($kpis['periode']['debut'] ?? now())->format('d/m/Y') }}
        au {{ \Carbon\Carbon::parse($kpis['periode']['fin'] ?? now())->format('d/m/Y') }}
      </span>
      {!! nl2br(htmlspecialchars($aiAnalysis)) !!}
    </div>
  </div>
  @endif

</div>

<div class="pdf-footer">
  <span>AEJ · Agence Emploi Jeunes · Côte d'Ivoire</span>
  <span>Rapport Global — Confidentiel</span>
  <span>{{ $generatedAt }}</span>
</div>

</body>
</html>
