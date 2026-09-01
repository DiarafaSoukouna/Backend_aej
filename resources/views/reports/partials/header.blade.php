{{-- Styles communs à tous les rapports PDF --}}
<style>
  @page { margin: 0; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 10px;
    color: #1E293B;
    background: #ffffff;
    line-height: 1.6;
  }

  /* ── Header ── */
  .pdf-header {
    background: linear-gradient(135deg, #0F2447 0%, #1d4ed8 60%, #3B82F6 100%);
    padding: 26px 36px 20px;
    position: relative;
    overflow: hidden;
  }
  .pdf-header::before {
    content: '';
    position: absolute;
    right: -50px; top: -50px;
    width: 180px; height: 180px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
  }
  .pdf-header::after {
    content: '';
    position: absolute;
    right: 60px; bottom: -40px;
    width: 120px; height: 120px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
  }
  .pdf-header .org-name {
    color: rgba(255,255,255,0.65);
    font-size: 7.5px;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 6px;
  }
  .pdf-header h1 {
    color: #ffffff;
    font-size: 19px;
    font-weight: bold;
    letter-spacing: 0.2px;
    line-height: 1.2;
  }
  .pdf-header .subtitle {
    color: rgba(255,255,255,0.75);
    font-size: 9px;
    margin-top: 5px;
    letter-spacing: 0.3px;
  }
  .pdf-header .header-badge {
    display: inline-block;
    background: rgba(255,255,255,0.15);
    color: #fff;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 7.5px;
    margin-top: 10px;
    border: 1px solid rgba(255,255,255,0.25);
    letter-spacing: 0.5px;
  }
  .meta-bar {
    background: #F8FAFC;
    border-bottom: 2px solid #E2E8F0;
    padding: 9px 36px;
    display: flex;
    justify-content: space-between;
    font-size: 8px;
    color: #64748B;
  }
  .meta-bar strong { color: #1E293B; }

  /* ── Contenu ── */
  .content { padding: 22px 36px 10px; }

  /* ── Sections ── */
  .section { margin-bottom: 22px; }
  .section-title {
    font-size: 10.5px;
    font-weight: bold;
    color: #0F2447;
    border-left: 4px solid #1d4ed8;
    padding-left: 9px;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* ── KPI Cards ── */
  .kpi-grid { width: 100%; border-collapse: separate; border-spacing: 6px 0; }
  .kpi-grid td { padding: 0 3px 0 0; width: 25%; vertical-align: top; }
  .kpi-card {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-top: 3px solid #1d4ed8;
    border-radius: 6px;
    padding: 12px 10px;
    text-align: center;
  }
  .kpi-card .kpi-value {
    font-size: 17px;
    font-weight: bold;
    color: #1d4ed8;
    display: block;
    line-height: 1.1;
  }
  .kpi-card .kpi-label {
    font-size: 7.5px;
    color: #64748B;
    margin-top: 4px;
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }
  .kpi-card.success { border-top-color: #10B981; }
  .kpi-card.success .kpi-value { color: #059669; }
  .kpi-card.warning { border-top-color: #F59E0B; }
  .kpi-card.warning .kpi-value { color: #D97706; }
  .kpi-card.danger  { border-top-color: #EF4444; }
  .kpi-card.danger  .kpi-value { color: #DC2626; }
  .kpi-card.info    { border-top-color: #0EA5E9; }
  .kpi-card.info    .kpi-value { color: #0284C7; }
  .kpi-highlight {
    background: linear-gradient(135deg, #0F2447 0%, #1d4ed8 100%);
    border-radius: 6px;
    padding: 14px 10px;
    color: white;
    text-align: center;
  }
  .kpi-highlight .h-value { font-size: 22px; font-weight: bold; display: block; line-height: 1.1; }
  .kpi-highlight .h-label { font-size: 7.5px; opacity: 0.8; margin-top: 4px; display: block; text-transform: uppercase; letter-spacing: 0.4px; }

  /* ── Tableaux ── */
  .data-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
  .data-table th {
    background: #0F2447;
    color: white;
    padding: 7px 10px;
    font-size: 8px;
    text-align: left;
    font-weight: bold;
    letter-spacing: 0.3px;
    text-transform: uppercase;
  }
  .data-table td {
    padding: 6px 10px;
    font-size: 8.5px;
    border-bottom: 1px solid #E2E8F0;
    color: #374151;
  }
  .data-table tr:nth-child(even) td { background: #F8FAFC; }
  .data-table tr:hover td { background: #EFF6FF; }

  /* ── Badges ── */
  .badge-success { background: #D1FAE5; color: #065F46; padding: 2px 7px; border-radius: 8px; font-size: 7.5px; font-weight: bold; }
  .badge-warning { background: #FEF3C7; color: #92400E; padding: 2px 7px; border-radius: 8px; font-size: 7.5px; font-weight: bold; }
  .badge-danger  { background: #FEE2E2; color: #991B1B; padding: 2px 7px; border-radius: 8px; font-size: 7.5px; font-weight: bold; }
  .badge-info    { background: #DBEAFE; color: #1E40AF; padding: 2px 7px; border-radius: 8px; font-size: 7.5px; font-weight: bold; }

  /* ── Bloc d'analyse ── */
  .analysis-block {
    background: linear-gradient(135deg, #F0F9FF 0%, #EFF6FF 100%);
    border: 1px solid #BFDBFE;
    border-left: 4px solid #1d4ed8;
    border-radius: 8px;
    padding: 14px 16px;
    font-size: 9px;
    line-height: 1.75;
    color: #1E293B;
    white-space: pre-wrap;
  }
  .analysis-block .analysis-label {
    font-size: 8px;
    font-weight: bold;
    color: #1d4ed8;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    margin-bottom: 8px;
    display: block;
    border-bottom: 1px solid #BFDBFE;
    padding-bottom: 6px;
  }

  /* ── Footer ── */
  .pdf-footer {
    background: #0F2447;
    padding: 10px 36px;
    display: flex;
    justify-content: space-between;
    font-size: 7.5px;
    color: rgba(255,255,255,0.6);
    margin-top: 28px;
  }
  .pdf-footer span:nth-child(2) { color: rgba(255,255,255,0.9); font-weight: bold; }

  /* ── Graphiques ── */
  .chart-box {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    margin-bottom: 12px;
  }
  .two-col { width: 100%; border-collapse: separate; border-spacing: 8px 0; }
  .two-col td { vertical-align: top; width: 50%; }

  /* ── Profil card ── */
  .profile-card {
    background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%);
    border: 1px solid #BFDBFE;
    border-radius: 10px;
    padding: 16px 18px;
    margin-bottom: 18px;
  }
  .profile-card .profile-name {
    font-size: 15px;
    font-weight: bold;
    color: #0F2447;
    letter-spacing: 0.3px;
  }
  .profile-card .profile-meta {
    font-size: 8.5px;
    color: #64748B;
    margin-top: 5px;
    line-height: 1.8;
  }
  .profile-card .profile-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 8px;
    font-weight: bold;
    margin-top: 7px;
  }

  /* ── Divider ── */
  .divider { border: none; border-top: 1px solid #E2E8F0; margin: 14px 0; }

  /* ── Période badge ── */
  .periode-badge {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    color: #fff;
    padding: 3px 12px;
    border-radius: 12px;
    font-size: 8px;
    margin-top: 8px;
    border: 1px solid rgba(255,255,255,0.3);
    font-weight: bold;
    letter-spacing: 0.5px;
  }
</style>
