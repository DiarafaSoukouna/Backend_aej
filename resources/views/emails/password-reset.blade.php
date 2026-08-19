<h1 style="
    margin:0 0 20px;
    color:#372E14;
    font-size:28px;
    font-weight:bold;
">
    Réinitialisation de votre mot de passe
</h1>

<p style="font-size:16px; line-height:1.6;">
    Bonjour,
</p>

<p style="font-size:16px; line-height:1.6;">
    Nous avons reçu une demande de réinitialisation de votre mot de passe
    pour votre compte sur <strong>AGENCE EMPLOI Jeunes</strong>.
</p>

<p style="font-size:16px; line-height:1.6;">
    Si vous êtes à l'origine de cette demande, cliquez sur le bouton ci-dessous
    pour définir un nouveau mot de passe :
</p>

<div style="text-align:center;margin:35px 0;">

    <a href="{{ $resetUrl }}"
       style="
           display:inline-block;
           padding:15px 35px;
           background:linear-gradient(135deg, #3AB3AA 0%, #2d8f85 100%);
           color:#ffffff;
           text-decoration:none;
           border-radius:8px;
           font-weight:bold;
           font-size:16px;
           box-shadow:0 4px 12px rgba(58, 179, 170, 0.3);
           transition:all 0.3s ease;
       ">
        Réinitialiser mon mot de passe
    </a>

</div>

<div style="
    background:#fff8e6;
    border-left:5px solid #f0ad4e;
    padding:20px;
    border-radius:8px;
    margin:25px 0;
">

    <h3 style="
        margin:0 0 10px;
        color:#8a6d3b;
        font-size:16px;
        font-weight:bold;
    ">
        Validité du lien
    </h3>

    <p style="margin:0; font-size:15px; line-height:1.5;">
        Ce lien de réinitialisation est valable pendant <strong>1 heure</strong>.
        Passé ce délai, vous devrez faire une nouvelle demande.
    </p>

</div>

<div style="
    background:#f8f9fa;
    border-left:5px solid #6c757d;
    padding:20px;
    border-radius:8px;
    margin:25px 0;
">

    <h3 style="
        margin:0 0 10px;
        color:#495057;
        font-size:16px;
        font-weight:bold;
    ">
        Conseils de sécurité
    </h3>

    <ul style="margin:0; padding-left:20px; font-size:15px; line-height:1.8;">
        <li>Choisissez un mot de passe unique que vous n'utilisez pas ailleurs</li>
        <li>Utilisez au moins 8 caractères avec majuscules, minuscules, chiffres et symboles</li>
        <li>Évitez les informations personnelles (dates, noms, etc.)</li>
        <li>Ne partagez jamais votre mot de passe</li>
    </ul>

</div>

<p style="font-size:16px; line-height:1.6; margin-top:30px;">
    Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :
</p>

<p style="
    font-size:13px;
    color:#555;
    word-break:break-all;
    background:#f8f9fa;
    padding:10px;
    border-radius:4px;
    margin:10px 0;
">
    {{ $resetUrl }}
</p>

<p style="
    color:#888;
    font-size:14px;
    line-height:1.5;
    margin-top:30px;
">
    Si vous n'êtes pas à l'origine de cette demande, aucune action n'est nécessaire.
    Votre compte reste sécurisé.
</p>