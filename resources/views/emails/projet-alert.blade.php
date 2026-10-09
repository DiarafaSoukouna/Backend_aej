<h1 style="
    margin:0 0 20px;
    color:#372E14;
    font-size:28px;
    font-weight:bold;
">
    Votre projet a été ajouté
</h1>

<p style="font-size:16px; line-height:1.6;">
    Bonjour,
</p>

<p style="font-size:16px; line-height:1.6;">
    Nous avons le plaisir de vous informer que votre projet a été ajouté avec succès
    dans notre système.
</p>

<div style="
    background:linear-gradient(135deg, #f0faf8 0%, #e8f5f3 100%);
    border-left:5px solid #3AB3AA;
    padding:25px;
    border-radius:8px;
    margin:30px 0;
    box-shadow:0 2px 8px rgba(58, 179, 170, 0.1);
">

    <h3 style="
        margin:0 0 15px;
        color:#276c67;
        font-size:18px;
        font-weight:bold;
    ">
        Détails du projet
    </h3>

    <table style="width:100%; border-collapse:collapse; margin-top:15px;">
        <tr>
            <td style="padding:10px 0; font-weight:bold; color:#372E14; width:40%;">Intitulé :</td>
            <td style="padding:10px 0; color:#555;">{{ $intitule }}</td>
        </tr>
        @if($matricule)
        <tr>
            <td style="padding:10px 0; font-weight:bold; color:#372E14;">Matricule :</td>
            <td style="padding:10px 0; color:#555;">{{ $matricule }}</td>
        </tr>
        @endif
        @if($montant)
        <tr>
            <td style="padding:10px 0; font-weight:bold; color:#372E14;">Montant :</td>
            <td style="padding:10px 0; color:#555;">{{ number_format($montant, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endif
    </table>

</div>

<div style="
    background:#e8f5e9;
    border-left:5px solid #4caf50;
    padding:20px;
    border-radius:8px;
    margin:25px 0;
">

    <h3 style="
        margin:0 0 10px;
        color:#2e7d32;
        font-size:16px;
        font-weight:bold;
    ">
        Prochaines étapes
    </h3>

    <p style="margin:0; font-size:15px; line-height:1.5;">
        Votre projet va maintenant suivre le processus de validation. Vous serez notifié
        de chaque étape de progression. Vous pouvez suivre l'avancement de votre projet
        directement sur la plateforme.
    </p>

</div>

<p style="font-size:16px; line-height:1.6; margin-top:30px;">
    Nous vous remercions de votre confiance et restons à votre disposition
    pour toute information.
</p>

<p style="font-size:16px; line-height:1.6;">
    Cordialement,
</p>

<p style="font-size:16px; line-height:1.6; margin:0;">
    <strong style="color:#3AB3AA;">L'équipe AGENCE EMPLOI Jeunes</strong>
</p>
