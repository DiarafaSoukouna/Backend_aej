<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de situation financière et Rappel d'échéances - {{ $projet['code'] ?? 'AEJ' }}</title>
    <style>
        @page {
            margin: 14mm 14mm 16mm 14mm;
            @bottom-right {
                content: "Page " counter(page) " sur " counter(pages);
                font-size: 9px;
                color: #64748b;
            }
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .header-title-main {
            font-size: 15px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-sub {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }
        .rep-civ {
            font-size: 10px;
            font-weight: bold;
            color: #334155;
            text-align: right;
        }
        .rep-devise {
            font-size: 8px;
            color: #64748b;
            font-style: italic;
            text-align: right;
        }
        .banner-report {
            background-color: #f8fafc;
            border-left: 4px solid #ef4444;
            padding: 10px 14px;
            margin-bottom: 16px;
            border-radius: 4px;
        }
        .banner-title {
            font-size: 14px;
            font-weight: bold;
            color: #991b1b;
            text-transform: uppercase;
        }
        .banner-meta {
            font-size: 10px;
            color: #475569;
            margin-top: 4px;
        }
        .badge-danger {
            display: inline-block;
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-warning {
            display: inline-block;
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .section-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 14px;
            overflow: hidden;
        }
        .section-header {
            background-color: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .section-body {
            padding: 10px 12px;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
        }
        .grid-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 10px;
        }
        .lbl {
            color: #64748b;
            font-weight: 500;
            width: 32%;
        }
        .val {
            color: #0f172a;
            font-weight: 600;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 4px;
        }
        .data-table th {
            background-color: #e2e8f0;
            color: #1e293b;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row {
            background-color: #fee2e2 !important;
            font-weight: bold;
            color: #991b1b;
        }
        .kpi-row-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
        }
        .kpi-num {
            font-size: 13px;
            font-weight: bold;
            color: #0f766e;
            margin-top: 2px;
        }
        .kpi-label {
            font-size: 8.5px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }
        .alert-callout {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 9.5px;
            color: #78350f;
            margin-top: 10px;
            line-height: 1.45;
        }
        .footer-signatures {
            margin-top: 18px;
            width: 100%;
            border-collapse: collapse;
        }
        .footer-signatures td {
            width: 50%;
            vertical-align: top;
            font-size: 9.5px;
            padding: 6px;
        }
    </style>
</head>
<body>

    {{-- EN-TETE OFFICIEL --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 60%;">
                <div class="header-title-main">Agence Emploi Jeunes (AEJ)</div>
                <div class="header-sub">Direction du Financement et de l'Accompagnement des Projets</div>
                <div class="header-sub">Service du Recouvrement et du Suivi des Engagements</div>
            </td>
            <td style="width: 40%; text-align: right;">
                <div class="rep-civ">RÉPUBLIQUE DE CÔTE D'IVOIRE</div>
                <div class="rep-devise">Union - Discipline - Travail</div>
                <div class="header-sub" style="margin-top: 5px;">Abidjan, le {{ now()->locale('fr')->isoFormat('DD MMMM YYYY') }}</div>
            </td>
        </tr>
    </table>

    {{-- BANNIERE RAPPORT --}}
    <div class="banner-report">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 75%;">
                    <div class="banner-title">
                        RAPPORT DE SITUATION FINANCIÈRE & AVIS DE RAPPEL D'ÉCHÉANCES
                    </div>
                    <div class="banner-meta">
                        Objet : <strong>Défaut de paiement sur trois (3) échéances consécutives échues</strong> &nbsp;|&nbsp; Dossier N° <strong>{{ $projet['code'] ?? ($projet['matricule'] ?? 'N/A') }}</strong>
                    </div>
                </td>
                <td style="width: 25%; text-align: right;">
                    <span class="badge-danger">
                        Rappel Officiel N° {{ $numero_rappel }} / {{ $max_rappels }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    {{-- IDENTIFICATION DU PROMOTEUR & DU PROJET EN 2 COLONNES --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 12px;">
        <tr>
            {{-- PROMOTEUR --}}
            <td style="width: 49%; vertical-align: top;">
                <div class="section-box" style="margin-bottom: 0;">
                    <div class="section-header">1. Identité du Promoteur</div>
                    <div class="section-body">
                        <table class="grid-table">
                            <tr>
                                <td class="lbl">Nom & Prénoms :</td>
                                <td class="val">{{ $promoteur['prenom'] ?? '' }} {{ $promoteur['nom'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Matricule AEJ :</td>
                                <td class="val">{{ $promoteur['matriculeaej'] ?? 'Non renseigné' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Pièce CNI/CMU :</td>
                                <td class="val">{{ $promoteur['numerocni'] ?? ($promoteur['numerocmu'] ?? 'Non renseigné') }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Téléphone :</td>
                                <td class="val">{{ $promoteur['telephone'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Email :</td>
                                <td class="val">{{ $promoteur['email'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Agence régionale :</td>
                                <td class="val">{{ $promoteur['agence_regionale']['nom'] ?? ($projet['agence']['nom'] ?? 'Agence locale') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>

            <td style="width: 2%;"></td>

            {{-- PROJET --}}
            <td style="width: 49%; vertical-align: top;">
                <div class="section-box" style="margin-bottom: 0;">
                    <div class="section-header">2. Fiche du Micro-Projet</div>
                    <div class="section-body">
                        <table class="grid-table">
                            <tr>
                                <td class="lbl">Code Projet :</td>
                                <td class="val">{{ $projet['code'] ?? ($projet['matricule'] ?? 'N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Intitulé :</td>
                                <td class="val">{{ $projet['intitule'] ?? 'Non spécifié' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Dispositif / Guichet :</td>
                                <td class="val">
                                    {{ $projet['dispositif']['nom'] ?? 'Financement standard' }}
                                    @if(!empty($projet['guichet']['nom']))
                                        / {{ $projet['guichet']['nom'] }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Organisme :</td>
                                <td class="val">{{ $projet['organisme']['nom'] ?? 'Partenaire financier AEJ' }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Montant accordé :</td>
                                <td class="val" style="color: #0f766e;">
                                    {{ number_format((float)($projet['montant_total'] ?? 0), 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                            <tr>
                                <td class="lbl">Statut dossier :</td>
                                <td class="val">{{ $projet['statut'] ?? 'En remboursement' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- SECTION 3 : TABLEAU OFFICIEL DES 3 ECHEANCES IMPAYEES --}}
    <div class="section-box">
        <div class="section-header" style="background-color: #fee2e2; color: #991b1b; border-bottom: 1px solid #f87171;">
            3. Détail des trois (3) échéances consécutives impayées et échues
        </div>
        <div class="section-body">
            <p style="margin: 0 0 8px 0; font-size: 9.5px; color: #475569;">
                Conformément aux clauses du contrat de prêt, le non-règlement de trois mensualités successives à leur date de maturité entraîne l'émission d'un rappel officiel. Voici le relevé des échéances échues constatées impayées :
            </p>

            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 12%;">N° Échéance</th>
                        <th style="width: 22%;">Date d'échéance</th>
                        <th class="text-right" style="width: 18%;">Montant échu</th>
                        <th class="text-right" style="width: 18%;">Montant versé</th>
                        <th class="text-right" style="width: 18%;">Reste dû</th>
                        <th class="text-center" style="width: 12%;">Jours retard</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($echeances as $echeance)
                    <tr>
                        <td class="text-center" style="font-weight: bold;">
                            Période {{ $echeance['periode'] ?? $loop->iteration }}
                        </td>
                        <td style="color: #dc2626; font-weight: 600;">
                            {{ !empty($echeance['date_echeance']) ? \Carbon\Carbon::parse($echeance['date_echeance'])->format('d/m/Y') : 'Échue' }}
                        </td>
                        <td class="text-right">
                            {{ number_format((float)($echeance['montant_echeance'] ?? $echeance['montant_echu'] ?? 0), 0, ',', ' ') }} FCFA
                        </td>
                        <td class="text-right" style="color: #16a34a;">
                            {{ number_format((float)($echeance['capital_rembourse'] ?? $echeance['montant_paye'] ?? 0), 0, ',', ' ') }} FCFA
                        </td>
                        <td class="text-right" style="font-weight: bold; color: #991b1b;">
                            {{ number_format((float)($echeance['reste_a_payer'] ?? $echeance['montant_impaye'] ?? $echeance['montant_echeance'] ?? 0), 0, ',', ' ') }} FCFA
                        </td>
                        <td class="text-center">
                            <span class="badge-danger">+{{ $echeance['jours_retard'] ?? 0 }} j</span>
                        </td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="4" class="text-right" style="padding: 8px;">
                            TOTAL IMMÉDIATEMENT EXIGIBLE (3 ÉCHÉANCES) :
                        </td>
                        <td class="text-right" style="padding: 8px; font-size: 11px;">
                            {{ number_format((float)$total_impaye, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="text-center" style="padding: 8px;">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECTION 4 : SYNTHESE DU PLAN D'AMORTISSEMENT GLOBAL --}}
    <div class="section-box">
        <div class="section-header">4. Synthèse financière globale du financement</div>
        <div class="section-body">
            <table class="kpi-row-table">
                <tr>
                    <td style="width: 25%; padding: 4px;">
                        <div class="kpi-card">
                            <div class="kpi-label">Montant Total Crédit</div>
                            <div class="kpi-num">{{ number_format((float)($synthese['montant_credit'] ?? $projet['montant_total'] ?? 0), 0, ',', ' ') }} F</div>
                        </div>
                    </td>
                    <td style="width: 25%; padding: 4px;">
                        <div class="kpi-card">
                            <div class="kpi-label">Total Déjà Remboursé</div>
                            <div class="kpi-num" style="color: #16a34a;">{{ number_format((float)($synthese['total_rembourse'] ?? 0), 0, ',', ' ') }} F</div>
                        </div>
                    </td>
                    <td style="width: 25%; padding: 4px;">
                        <div class="kpi-card">
                            <div class="kpi-label">Reste Total Dû</div>
                            <div class="kpi-num" style="color: #dc2626;">{{ number_format((float)($synthese['reste_global'] ?? 0), 0, ',', ' ') }} F</div>
                        </div>
                    </td>
                    <td style="width: 25%; padding: 4px;">
                        <div class="kpi-card">
                            <div class="kpi-label">Taux d'Exécution</div>
                            <div class="kpi-num">{{ number_format((float)($synthese['taux_remboursement'] ?? 0), 1, ',', ' ') }} %</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- SECTION 5 : AVERTISSEMENT JURIDIQUE & MODALITES DE REGULARISATION --}}
    <div class="section-box" style="margin-bottom: 0;">
        <div class="section-header">5. Procédure et Conséquences en cas de non-paiement</div>
        <div class="section-body" style="font-size: 9.5px; color: #334155;">
            <p style="margin: 0 0 6px 0;">
                Conformément à la politique de gestion et d'accompagnement de l'Agence Emploi Jeunes :
            </p>
            <ul style="margin: 0; padding-left: 18px; line-height: 1.5;">
                <li><strong>Cycle des rappels :</strong> Un rappel officiel est adressé à chaque début de semaine jusqu'à concurrence de <strong>cinq (5) rappels au maximum</strong>. Le présent document constitue le <strong>rappel officiel n° {{ $numero_rappel }} sur 5</strong>.</li>
                <li><strong>Conséquence au 5<sup>ème</sup> rappel :</strong> À l'issue du 5<sup>ème</sup> rappel resté infructueux sans plan d'apurement validé, la déchéance du terme sera prononcée et l'intégralité du solde résiduel du prêt deviendra immédiatement exigible. Le dossier sera transmis à la Direction Juridique et aux Huissiers de Justice / Cabinets de Recouvrement assermentés.</li>
                <li><strong>Modalités de paiement :</strong> Règlement par versement direct ou virement sur le compte bancaire de l'organisme de financement partenaire <em>{{ $projet['organisme']['nom'] ?? 'AEJ' }}</em>, ou auprès de la caisse de votre Agence Régionale AEJ de rattachement.</li>
            </ul>

            <div class="alert-callout">
                <strong>Important :</strong> Afin que votre versement soit convenablement imputé et que la suspension des rappels automatiques soit immédiate, merci d'adresser une copie lisible de votre bordereau de versement par courriel ou directement à votre conseiller de projet.
            </div>
        </div>
    </div>

    {{-- SIGNATURES --}}
    <table class="footer-signatures">
        <tr>
            <td style="text-align: left;">
                <div style="font-weight: bold; color: #0f766e; text-transform: uppercase;">Pour le Promoteur</div>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 3px;">Lu et pris connaissance des échéances en retard</div>
                <div style="margin-top: 25px; border-bottom: 1px dotted #94a3b8; width: 170px;"></div>
                <div style="font-size: 8px; color: #94a3b8; margin-top: 2px;">Date et signature</div>
            </td>
            <td style="text-align: right;">
                <div style="font-weight: bold; color: #0f766e; text-transform: uppercase;">Pour l'Agence Emploi Jeunes</div>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 3px;">Le Responsable du Pôle Recouvrement & Financement</div>
                <div style="margin-top: 25px; display: inline-block; border-bottom: 1px dotted #94a3b8; width: 170px;"></div>
                <div style="font-size: 8px; color: #94a3b8; margin-top: 2px;">Cachet et Visa officiel AEJ</div>
            </td>
        </tr>
    </table>

</body>
</html>

