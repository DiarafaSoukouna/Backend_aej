<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Bulletin {{ ucfirst($bulletin->periode_type) }} — {{ $bulletin->periode_debut->isoFormat('MMMM YYYY') }}</title>
  @include('reports.partials.header')
  <style>
    .kpi-highlight {
      background: linear-gradient(135deg, #0F2447 0%, #1d4ed8 100%);
      border-radius: 6px;
      padding: 14px 10px;
      color: white;
      text-align: center;
    }
    .kpi-highlight .h-value { font-size: 24px; font-weight: bold; display: block; line-height: 1.1; }
    .kpi-highlight .h-label { font-size: 7.5px; opacity: 0.8; margin-top: 4px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
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
  <h1>Bulletin {{ ucfirst($bulletin->periode_type) }} de Suivi</h1>
  <div class="subtitle">
    Performance financière · Indicateurs clés · Analyse du portefeuille ·
    {{ $bulletin->organisme ? $bulletin->organisme->nom : 'Tous organismes confondus' }}
  </div>
  <span class="periode-badge">
    Période couverte : {{ $bulletin->periode_debut->format('d/m/Y') }} — {{ $bulletin->periode_fin->format('d/m/Y') }}
  </span>
</div>

<div class="meta-bar">
  <span>Bulletin N° <strong>{{ $bulletin->id }}</strong> &nbsp;·&nbsp; Type : <strong>{{ ucfirst($bulletin->periode_type) }}</strong></span>
  <span>Généré le : <strong>{{ $generatedAt }}</strong></span>
  <span>Document confidentiel — Usage interne AEJ</span>
</div>

<div class="content">

  {{-- ── Résumé exécutif ── --}}
  @if(!empty($bulletin->commentaire_ia))
  <div class="summary-intro">
    <strong>Résumé exécutif —</strong>
    {{ Str::limit(strip_tags($bulletin->commentaire_ia), 400) }}
  </div>
  @endif

  {{-- ── KPIs headline ── --}}
  <div class="section">
    <div class="section-title">Indicateurs de performance clés</div>
    <table style="width:100%; border-collapse:separate; border-spacing:6px 0;">
      <tr>
        <td style="width:25%;">
          <div class="kpi-highlight">
            <span class="h-value">{{ number_format($kpis['taux_remboursement'] ?? 0, 1) }}%</span>
            <span class="h-label">Taux de remboursement global</span>
          </div>
        </td>
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
          <div class="kpi-card success">
            <span class="kpi-value" style="font-size:12px;">{{ number_format($kpis['montant_paye'] ?? 0, 0, ',', ' ') }}</span>
            <span class="kpi-label">Montant recouvré (FCFA)</span>
          </div>
        </td>
      </tr>
    </table>
  </div>

  {{-- ── Flux financiers ── --}}
  <div class="section" style="margin-top:-8px;">
    <table style="width:100%; border-collapse:separate; border-spacing:6px 0;">
      <tr>
        <td style="width:25%;">
          <div class="kpi-card">
            <span class="kpi-value" style="font-size:12px;">{{ number_format($kpis['budget_total_accorde'] ?? 0, 0, ',', ' ') }}</span>
            <span class="kpi-label">Budget total accordé (FCFA)</span>
          </div>
        </td>
        <td style="width:25%;">
          <div class="kpi-card">
            <span class="kpi-value" style="font-size:12px;">{{ number_format($kpis['montant_prevu'] ?? 0, 0, ',', ' ') }}</span>
            <span class="kpi-label">Montant attendu (FCFA)</span>
          </div>
        </td>
        <td style="width:25%;">
          <div class="kpi-card danger">
            <span class="kpi-value" style="font-size:12px;">{{ number_format($kpis['montant_impaye'] ?? 0, 0, ',', ' ') }}</span>
            <span class="kpi-label">Montant impayé (FCFA)</span>
          </div>
        </td>
        <td style="width:25%;"></td>
      </tr>
    </table>
  </div>

  {{-- ── Taux de recouvrement visuel ── --}}
  @php
    $taux = min(100, max(0, $kpis['taux_remboursement'] ?? 0));
    $barClass = $taux >= 80 ? '' : ($taux >= 50 ? 'warning' : 'danger');
  @endphp
  <div class="section">
    <div class="section-title">Progression du recouvrement</div>
    <table style="width:100%; border-collapse:collapse;">
      <tr>
        <td style="width:80%; padding-right:12px; vertical-align:middle;">
          <div class="progress-bar-wrap">
            <div class="progress-bar-fill {{ $barClass }}" style="width: {{ $taux }}%;"></div>
          </div>
        </td>
        <td style="font-size:14px; font-weight:bold; color: {{ $taux >= 80 ? '#059669' : ($taux >= 50 ? '#D97706' : '#DC2626') }}; width:20%; text-align:right;">
          {{ $taux }}%
        </td>
      </tr>
    </table>
    <div style="font-size:8px; color:#64748B; margin-top:4px;">
      Sur {{ number_format($kpis['montant_prevu'] ?? 0, 0, ',', ' ') }} FCFA attendus,
      {{ number_format($kpis['montant_paye'] ?? 0, 0, ',', ' ') }} FCFA ont été recouvrés.
    </div>
  </div>

  {{-- ── Graphiques ── --}}
  @if(!empty($charts))
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
  </div>
  @endif

  {{-- ── Répartition des bénéficiaires ── --}}
  @php $cat = $bulletin->categorisation_payeurs ?? []; @endphp
  @if(!empty($cat))
  <div class="section">
    <div class="section-title">Répartition des bénéficiaires par comportement de remboursement</div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Catégorie</th>
          <th style="text-align:center;">Effectif</th>
          <th style="text-align:center;">Part du portefeuille</th>
          <th>Critère d'éligibilité</th>
          <th>Niveau d'alerte</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><span class="badge-success">Bons payeurs</span></td>
          <td style="text-align:center;"><strong>{{ $cat['bons_payeurs']['count'] ?? 0 }}</strong></td>
          <td style="text-align:center;">{{ $cat['bons_payeurs']['taux_pct'] ?? 0 }}%</td>
          <td>Taux ≥ 95%</td>
          <td style="color:#059669;">✓ Aucune alerte</td>
        </tr>
        <tr>
          <td><span class="badge-info">Payeurs réguliers</span></td>
          <td style="text-align:center;"><strong>{{ $cat['payeurs_moyens']['count'] ?? 0 }}</strong></td>
          <td style="text-align:center;">{{ $cat['payeurs_moyens']['taux_pct'] ?? 0 }}%</td>
          <td>70% à 94%</td>
          <td style="color:#0284C7;">i Surveillance normale</td>
        </tr>
        <tr>
          <td><span class="badge-warning">En situation de retard</span></td>
          <td style="text-align:center;"><strong>{{ $cat['en_retard']['count'] ?? 0 }}</strong></td>
          <td style="text-align:center;">{{ $cat['en_retard']['taux_pct'] ?? 0 }}%</td>
          <td>30% à 69%</td>
          <td style="color:#D97706;">⚠ Suivi rapproché requis</td>
        </tr>
        <tr>
          <td><span class="badge-danger">Défaillants</span></td>
          <td style="text-align:center;"><strong>{{ $cat['defaillants']['count'] ?? 0 }}</strong></td>
          <td style="text-align:center;">{{ $cat['defaillants']['taux_pct'] ?? 0 }}%</td>
          <td>< 30%</td>
          <td style="color:#DC2626;">✕ Action de recouvrement urgente</td>
        </tr>
      </tbody>
    </table>
  </div>
  @endif

  {{-- ── Répartition par secteur ── --}}
  @if(!empty($kpis['par_secteur']))
  <div class="section">
    <div class="section-title">Activité par secteur d'activité (Top 6)</div>
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Secteur</th>
          <th style="text-align:center;">Nombre de projets</th>
          <th style="text-align:right;">Montant total (FCFA)</th>
        </tr>
      </thead>
      <tbody>
        @foreach(array_slice((array)$kpis['par_secteur'], 0, 6) as $idx => $s)
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
  @if(!empty($bulletin->commentaire_ia))
  <div class="section">
    <div class="section-title">Analyse du portefeuille & Recommandations stratégiques</div>
    <div class="analysis-block">
      <span class="analysis-label">Analyse détaillée — Période {{ $bulletin->periode_debut->format('d/m/Y') }} au {{ $bulletin->periode_fin->format('d/m/Y') }}</span>
      {!! nl2br(htmlspecialchars($bulletin->commentaire_ia)) !!}
    </div>
  </div>
  @endif

</div>

<div class="pdf-footer">
  <span>AEJ · Agence Emploi Jeunes · Côte d'Ivoire</span>
  <span>Bulletin {{ ucfirst($bulletin->periode_type) }} — Confidentiel</span>
  <span>{{ $generatedAt }}</span>
</div>

</body>
</html>
