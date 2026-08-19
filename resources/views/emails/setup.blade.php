<h1 style="
    margin:0 0 20px;
    color:#372E14;
    font-size:28px;
    font-weight:bold;
">
    Configuration de votre compte
</h1>

<p style="font-size:16px; line-height:1.6;">
    Bonjour
    <strong style="color:#3AB3AA;">
        {{ $personnel->prenom }} {{ $personnel->nom }}
    </strong>,
</p>

<p style="font-size:16px; line-height:1.6;">
    Votre compte sur <strong>AGENCE EMPLOI Jeunes</strong> a été créé avec succès.
    Pour finaliser votre inscription et accéder à la plateforme, vous devez définir votre mot de passe.
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
        Définissez votre mot de passe
    </h3>

    <p style="font-size:15px; line-height:1.6; margin-bottom:20px;">
        Cliquez sur le bouton ci-dessous pour définir votre mot de passe sécurisé :
    </p>

    <div style="text-align:center;">

        <a href="{{ $setupUrl }}"
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
           ">
            Configurer mon mot de passe
        </a>

    </div>

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
        Ce lien de configuration est valable pendant <strong>24 heures</strong>.
        Passé ce délai, vous devrez contacter l'administrateur pour obtenir un nouveau lien.
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
    {{ $setupUrl }}
</p>

<p style="font-size:16px; line-height:1.6; margin-top:30px;">
    Une fois votre mot de passe défini, vous pourrez vous connecter à la plateforme
    et accéder à tous les outils de <strong>l'Agence Emploi Jeunes</strong>.
</p>

<p style="font-size:16px; line-height:1.6;">
    Nous vous souhaitons une excellente expérience au sein de notre plateforme.
</p>

<p style="font-size:16px; line-height:1.6;">
    Cordialement,
</p>

<p style="font-size:16px; line-height:1.6; margin:0;">
    <strong style="color:#3AB3AA;">L'équipe AGENCE EMPLOI Jeunes</strong>
</p>