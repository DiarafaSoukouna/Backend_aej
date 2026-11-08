<div style="margin-bottom: 25px;">
    <span style="
        display: inline-block;
        background: #dc3545;
        color: #ffffff;
        font-weight: bold;
        font-size: 13px;
        padding: 6px 14px;
        border-radius: 20px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    ">
        Rappel officiel {{ $numero_rappel }} / {{ $max_rappels }}
    </span>
</div>

<h1 style="
    margin: 0 0 20px;
    color: #372E14;
    font-size: 24px;
    font-weight: bold;
    line-height: 1.3;
">
    Avis de retard de remboursement — 3 échéances impayées
</h1>

<p style="font-size: 15px; line-height: 1.6; color: #333333;">
    Bonjour <strong>{{ $promoteur['prenom'] ?? '' }} {{ $promoteur['nom'] ?? '' }}</strong>,
</p>

<p style="font-size: 15px; line-height: 1.6; color: #555555;">
    Sauf erreur de notre part, les écritures comptables indiquent que <strong>trois (3) échéances de suite</strong> liées au financement de votre micro-projet demeurent <strong>impayées</strong> alors que leur date limite de remboursement est d'ores et déjà dépassée.
</p>

{{-- BLOC PROJET --}}
<div style="
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 18px 22px;
    margin: 20px 0;
">
    <div style="font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 6px;">
        Projet concerné
    </div>
    <div style="font-size: 18px; font-weight: bold; color: #1e293b;">
        {{ $projet['intitule'] ?? 'Projet de financement' }}
    </div>
    <div style="font-size: 13px; color: #64748b; margin-top: 4px;">
        Code dossier : <strong>{{ $projet['code'] ?? ($projet['matricule'] ?? 'N/A') }}</strong>
        @if(!empty($projet['organisme']))
            &nbsp;•&nbsp; Organisme : <strong>{{ $projet['organisme']['nom'] ?? $projet['organisme'] }}</strong>
        @endif
        @if(!empty($projet['montant_total']))
            &nbsp;•&nbsp; Financement total : <strong>{{ number_format((float)$projet['montant_total'], 0, ',', ' ') }} FCFA</strong>
        @endif
    </div>
</div>

{{-- TABLEAU DES 3 ECHEANCES IMPAYEES --}}
<div style="margin: 25px 0;">
    <h3 style="
        font-size: 15px;
        color: #372E14;
        margin-bottom: 12px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    ">
        Détail des trois (3) échéances en souffrance :
    </h3>

    <table width="100%" cellpadding="0" cellspacing="0" style="
        border-collapse: collapse;
        font-size: 13px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    ">
        <thead>
            <tr style="background: #f3f4f6; color: #374151; text-align: left;">
                <th style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb;">N° Échéance</th>
                <th style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb;">Date d'échéance</th>
                <th style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: right;">Montant dû</th>
                <th style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: right;">Reste impayé</th>
                <th style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: center;">Retard</th>
            </tr>
        </thead>
        <tbody>
            @foreach($echeances as $echeance)
            <tr style="border-bottom: 1px solid #f1f5f9; background: #ffffff;">
                <td style="padding: 10px 12px; font-weight: bold; color: #1f2937;">
                    Échéance {{ $echeance['periode'] ?? $loop->iteration }}
                </td>
                <td style="padding: 10px 12px; color: #dc2626; font-weight: 500;">
                    {{ !empty($echeance['date_echeance']) ? \Carbon\Carbon::parse($echeance['date_echeance'])->locale('fr')->isoFormat('DD MMMM YYYY') : 'Dépassée' }}
                </td>
                <td style="padding: 10px 12px; text-align: right; color: #4b5563;">
                    {{ number_format((float)($echeance['montant_echeance'] ?? $echeance['montant_echu'] ?? 0), 0, ',', ' ') }} FCFA
                </td>
                <td style="padding: 10px 12px; text-align: right; font-weight: bold; color: #dc2626;">
                    {{ number_format((float)($echeance['reste_a_payer'] ?? $echeance['montant_impaye'] ?? $echeance['montant_echeance'] ?? 0), 0, ',', ' ') }} FCFA
                </td>
                <td style="padding: 10px 12px; text-align: center;">
                    <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                        +{{ $echeance['jours_retard'] ?? 0 }} j
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- TOTAL DU --}}
<div style="
    background: linear-gradient(135deg, #fff5f5 0%, #fee2e2 100%);
    border: 2px solid #ef4444;
    border-radius: 10px;
    padding: 20px;
    margin: 25px 0;
    text-align: center;
">
    <div style="font-size: 13px; color: #7f1d1d; font-weight: bold; text-transform: uppercase;">
        Montant total immédiatement exigible
    </div>
    <div style="font-size: 28px; font-weight: bold; color: #991b1b; margin-top: 5px;">
        {{ number_format((float)$total_impaye, 0, ',', ' ') }} FCFA
    </div>
    <div style="font-size: 12px; color: #991b1b; margin-top: 4px;">
        (Cumul des 3 échéances consécutives impayées)
    </div>
</div>

{{-- PIECE JOINTE PDF ANNONCEE --}}
<div style="
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 16px 20px;
    border-radius: 6px;
    margin: 25px 0;
">
    <div style="font-weight: bold; color: #1e40af; font-size: 14px; margin-bottom: 4px;">
        📎 Rapport de situation financière inclus en pièce jointe
    </div>
    <p style="margin: 0; font-size: 13px; color: #1e3a8a; line-height: 1.5;">
        Un <strong>rapport détaillé et complet au format PDF</strong> est joint à cet email. Il récapitule l'état intégral de votre compte de remboursement, vos indicateurs de suivi et la marche à suivre pour régulariser votre dossier.
    </p>
</div>

{{-- AVERTISSEMENT PROCEDURE 5 RAPPELS --}}
<div style="
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
    padding: 15px 18px;
    margin: 20px 0;
    font-size: 13px;
    color: #92400e;
    line-height: 1.6;
">
    <strong>⚠️ Procédure de rappel :</strong><br>
    Ce rappel vous est adressé au titre du rappel <strong>n° {{ $numero_rappel }} sur un total de {{ $max_rappels }} rappels hebdomadaires</strong>.
    Au terme du 5<sup>ème</sup> rappel sans régularisation de votre part, votre dossier sera immédiatement transmis à la <strong>direction du recouvrement et aux services contentieux</strong> pour mise en recouvrement forcé.
</div>

{{-- INSTRUCTIONS REGULARISATION --}}
<div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
    <h4 style="margin: 0 0 10px; color: #1f2937; font-size: 14px;">Que devez-vous faire ?</h4>
    <ol style="margin: 0; padding-left: 20px; font-size: 13px; color: #4b5563; line-height: 1.7;">
        <li>Effectuer sans délai le règlement des échéances impayées auprès de votre organisme financier partenaire ou agence régionale AEJ.</li>
        <li>Transmettre la preuve de paiement à votre conseiller en charge du suivi de votre projet.</li>
        <li>En cas de difficultés temporaires, contactez immédiatement votre agence régionale pour convenir d'un plan d'aménagement.</li>
    </ol>
</div>

<p style="margin-top: 30px; font-size: 13px; color: #6b7280; line-height: 1.5;">
    Restant à votre disposition pour vous accompagner dans la réussite et la pérennité de votre activité,<br>
    <strong>L'Agence Emploi Jeunes (AEJ)</strong>
</p>

