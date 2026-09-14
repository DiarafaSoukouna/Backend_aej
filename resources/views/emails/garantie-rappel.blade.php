<h1 style="
    margin:0 0 20px;
    color:#372E14;
    font-size:28px;
    font-weight:bold;
">
    Rappel de paiement de garantie
</h1>

<p style="font-size:16px; line-height:1.6;">
    Bonjour,
</p>

<p style="font-size:16px; line-height:1.6;">
    Nous vous rappelons qu'une garantie doit être payée pour votre micro-projet
    <strong>{{ $micro_projet_code }}</strong>.
</p>

<div style="
    background:linear-gradient(135deg, #fff8e6 0%, #fff3cd 100%);
    border:2px solid #f0ad4e;
    border-radius:12px;
    padding:30px;
    margin:30px 0;
    box-shadow:0 4px 12px rgba(240, 173, 78, 0.15);
">

    <div style="margin-bottom:20px;">
        <span style="font-size:14px; color:#6c757d; font-weight:bold;">Montant de la garantie :</span>
        <div style="font-size:32px; font-weight:bold; color:#372E14; margin-top:5px;">
            {{ number_format($montant, 0, ',', ' ') }} FCFA
        </div>
    </div>

    <div style="margin-bottom:20px;">
        <span style="font-size:14px; color:#6c757d; font-weight:bold;">Organisme de financement :</span>
        <div style="font-size:18px; font-weight:bold; color:#372E14; margin-top:5px;">
            {{ $organisme }}
        </div>
    </div>

    <div style="margin-bottom:20px;">
        <span style="font-size:14px; color:#6c757d; font-weight:bold;">Date de rappel :</span>
        <div style="font-size:18px; color:#372E14; margin-top:5px;">
            {{ $date_rappel ? \Carbon\Carbon::parse($date_rappel)->locale('fr')->isoFormat('DD MMMM YYYY') : 'Non définie' }}
        </div>
    </div>

    <div>
        <span style="font-size:14px; color:#6c757d; font-weight:bold;">Statut :</span>
        <div style="margin-top:5px;">
            @if($statut === 'EN_ATTENTE')
                <span style="background:#ffc107; color:#372E14; padding:8px 16px; border-radius:20px; font-size:14px; font-weight:bold;">
                    En attente de paiement
                </span>
            @elseif($statut === 'PAYE')
                <span style="background:#28a745; color:white; padding:8px 16px; border-radius:20px; font-size:14px; font-weight:bold;">
                    Payé
                </span>
            @elseif($statut === 'PARTIEL')
                <span style="background:#17a2b8; color:white; padding:8px 16px; border-radius:20px; font-size:14px; font-weight:bold;">
                    Paiement partiel
                </span>
            @else
                <span style="background:#dc3545; color:white; padding:8px 16px; border-radius:20px; font-size:14px; font-weight:bold;">
                    Non payé
                </span>
            @endif
        </div>
    </div>

</div>

<div style="
    background:#e8f5f3;
    border-left:5px solid #3AB3AA;
    padding:20px;
    border-radius:8px;
    margin:25px 0;
">

    <h3 style="
        margin:0 0 10px;
        color:#372E14;
        font-size:16px;
        font-weight:bold;
    ">
        Instructions de paiement
    </h3>

    <p style="margin:0; font-size:15px; line-height:1.5;">
        Veuillez procéder au paiement de la garantie dans les meilleurs délais.
        Pour toute question concernant le paiement, n'hésitez pas à contacter notre équipe.
    </p>

</div>

<p style="
    color:#888;
    font-size:14px;
    line-height:1.5;
    margin-top:30px;
">
    Si vous avez déjà effectué ce paiement, vous pouvez ignorer cet e-mail.
</p>
