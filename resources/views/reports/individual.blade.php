<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Rapport Individuel — {{ $profile['prenom'] ?? '' }} {{ $profile['nom'] ?? '' }}</title>
  @include('reports.partials.header')
  <style>
    .risk-score-bar { background: #E2E8F0; border-radius: 6px; height: 10px; width: 100%; margin-top: 5px; }
    .risk-score-fill { height: 10px; border-radius: 6px; }
    .info-row { display: flex; justify-content: space-between; font-size: 8.5px; color: #64748B; margin-top: 3px; }
  </style>
</head>
<body>

{{-- ═══ HEADER ═══ --}}
<div class="pdf-header">
  <div class="org-name">Agence Emploi Jeunes (AEJ) · République de Côte d'Ivoire</div>
  <h1>Rapport Individuel Bénéficiaire</h1>
  <div class="subtitle">Analyse complète du profil · Financements · Remboursements · Évaluation des risques</div>
  <span class="header-badge">Dossier individuel — Usage interne</span>
</div>

<div class="meta-bar">
  <span>Matricule AEJ : <strong>{{ $profile['matriculeaej'] ?? 'N/A' }}</strong></span>
  <span>Généré le : <strong>{{ $generatedAt }}</strong></span>
  <span>Rapport confidentiel — Usage interne AEJ</span>
</div>

<div class="content">

  {{-- ── Profil du bénéficiaire ── --}}
  <div class="profile-card">
    <table style="width:100%;">
      <tr>
        <td style="width:65%; vertical-align:top;">
          <div class="profile-name">{{ $profile['prenom'] ?? '' }} {{ strtoupper($profile['nom'] ?? '') }}</div>
          <div class="profile-meta">
            <strong>Matricule :</strong> {{ $profile['matriculeaej'] ?? 'N/A' }}<br>
            <strong>Téléphone :</strong> {{ $profile['telephone'] ?? 'Non renseigné' }} &nbsp;·&nbsp;
            <strong>Email :</strong> {{ $profile['email'] ?? 'Non renseigné' }}<br>
            <strong>Statut :</strong> {{ $profile['statut'] ?? 'N/A' }}
          </div>
          @php
            $cat = $kpis['categorie_payeur'] ?? 'N/A';
            $catClass = match($cat) {
              'Bon payeur'      => 'badge-success',
              'Payeur régulier' => 'badge-info',
              'En retard'       => 'badge-warning',
              default           => 'badge-danger',
            };
          @endphp
          <span class="profile-badge {{ $catClass }}">{{ $cat }}</span>
        </td>
        <td style="width:35%; text-align:right; vertical-align:top;">
          @php $score = $kpis['score_risque'] ?? 0; @endphp
          <div style="font-size:8px; color:#64748B; text-align:center; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.5px;">Score de risque</div>
          <div style="font-size:28px; font-weight:bold; text-align:center; color: {{ $score > 50 ? '#DC2626' : ($score > 20 ? '#D97706' : '#059669') }};">
            {{ $score }}/100
          </div>
          <div class="risk-score-bar">
            <div class="risk-score-fill" style="width:{{ $score }}%; background: {{ $score > 50 ? '#EF4444' : ($score > 20 ? '#F59E0B' : '#10B981') }};"></div>
          </div>
          <div style="font-size:8px; text-align:center; margin-top:4px; font-weight:bold; color: {{ $score > 50 ? '#DC2626' : ($score > 20 ? '#D97706' : '#059669') }};">
            {{ $kpis['niveau_risque'] ?? 'N/A' }}
          </div>
        </td>
      </tr>
    </table>
  </div>

  {{-- ── KPIs clés ── --}}
  <div class="section">
    <div class="section-title">Indicateurs clés du dossier</div>
    <table style="width:100%; border-collapse:separate; border-spacing:6px 0;">
      <tr>
        <td style="width:25%;">
          <div class="kpi-card">
            <span class="kpi-value" style="font-size:13px;">{{ number_format($kpis['budget_total_accorde'] ?? 0, 0, ',', ' ') }}</span>
            <span class="kpi-label">Montant accordé (FCFA)</span>
          </div>
        </td>
        <td style="width:25%;">
          <div class="kpi-card {{ ($kpis['taux_remboursement'] ?? 0) >= 80 ? 'success' : (($kpis['taux_remboursement'] ?? 0) >= 50 ? 'warning' : 'danger') }}">
            <span class="kpi-value">{{ $kpis['taux_remboursement'] ?? 0 }}%</span>
            <span class="kpi-label">Taux de remboursement</span>
          </div>
        </td>
        <td style="width:25%;">
          <div class="kpi-card {{ ($kpis['nombre_retards'] ?? 0) > 0 ? 'warning' : 'success' }}">
            <span class="kpi-value">{{ $kpis['nombre_retards'] ?? 0 }}</span>
            <span class="kpi-label">Retards de paiement</span>
          </div>
        </td>
        <td style="width:25%;">
          <div class="kpi-card info">
            <span class="kpi-value">{{ $kpis['nombre_projets'] ?? 0 }}</span>
            <span class="kpi-label">Projets financés</span>
          </div>
        </td>
      </tr>
    </table>
  </div>

  {{-- ── Détail financier ── --}}
  <div class="section">
    <div class="section-title">Détail financier du remboursement</div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Indicateur financier</th>
          <th style="text-align:right;">Montant (FCFA)</th>
          <th>Observations</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Montant total prévu (échéances cumulées)</td>
          <td style="text-align:right;"><strong>{{ number_format($kpis['montant_prevu'] ?? 0, 0, ',', ' ') }}</strong></td>
          <td style="color:#64748B;">Planifié dans le calendrier de remboursement</td>
        </tr>
        <tr>
          <td>Montant effectivement payé</td>
          <td style="text-align:right; color:#059669;"><strong>{{ number_format($kpis['montant_paye'] ?? 0, 0, ',', ' ') }}</strong></td>
          <td style="color:#059669;">Versements confirmés</td>
        </tr>
        <tr>
          <td>Montant impayé restant</td>
          <td style="text-align:right; color:{{ ($kpis['montant_impaye'] ?? 0) > 0 ? '#DC2626' : '#059669' }};"><strong>{{ number_format($kpis['montant_impaye'] ?? 0, 0, ',', ' ') }}</strong></td>
          <td style="color:{{ ($kpis['montant_impaye'] ?? 0) > 0 ? '#DC2626' : '#059669' }};">{{ ($kpis['montant_impaye'] ?? 0) > 0 ? 'À recouvrer' : 'Aucun impayé' }}</td>
        </tr>
        <tr>
          <td>Budget total accordé</td>
          <td style="text-align:right;"><strong>{{ number_format($kpis['budget_total_accorde'] ?? 0, 0, ',', ' ') }}</strong></td>
          <td style="color:#64748B;">Montant de financement initial</td>
        </tr>
      </tbody>
    </table>
  </div>

  {{-- ── Graphique ── --}}
  @if(!empty($charts['remboursements']))
  <div class="section">
    <div class="section-title">Évolution mensuelle des remboursements</div>
    <div class="chart-box">{!! $charts['remboursements'] !!}</div>
  </div>
  @endif

  {{-- ── Exploitation ── --}}
  @if(!empty($kpis['chiffre_affaires']) || !empty($kpis['nbre_emplois']))
  <div class="section">
    <div class="section-title">Données d'exploitation de l'entreprise</div>
    <table style="width:100%; border-collapse:separate; border-spacing:6px 0;">
      <tr>
        <td style="width:33%;">
          <div class="kpi-card success">
            <span class="kpi-value" style="font-size:13px;">{{ number_format($kpis['chiffre_affaires'] ?? 0, 0, ',', ' ') }}</span>
            <span class="kpi-label">Chiffre d'affaires (FCFA)</span>
          </div>
        </td>
        <td style="width:33%;">
          <div class="kpi-card info">
            <span class="kpi-value">{{ $kpis['nbre_emplois'] ?? 0 }}</span>
            <span class="kpi-label">Emplois créés / maintenus</span>
          </div>
        </td>
        <td style="width:33%;"></td>
      </tr>
    </table>
  </div>
  @endif

  {{-- ── Analyse & Recommandations ── --}}
  <div class="section">
    <div class="section-title">Analyse du dossier & Recommandations</div>
    <div class="analysis-block">
      <span class="analysis-label">Analyse — {{ $profile['prenom'] ?? '' }} {{ strtoupper($profile['nom'] ?? '') }} · Matricule {{ $profile['matriculeaej'] ?? 'N/A' }}</span>
      {!! nl2br(htmlspecialchars($aiComment)) !!}
    </div>
  </div>

</div>

<div class="pdf-footer">
  <span>AEJ · Agence Emploi Jeunes · Côte d'Ivoire</span>
  <span>Rapport individuel — Confidentiel</span>
  <span>{{ $generatedAt }}</span>
</div>

</body>
</html>
