<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>{{ $subject ?? config('app.name') }}</title>
</head>

<body style="
    margin:0;
    padding:0;
    background:#f4f7f9;
    font-family:Arial, Helvetica, sans-serif;
">

    <table width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="background:#f4f7f9;padding:30px 10px;">

        <tr>
            <td align="center">

                <table width="600"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="
                       width:100%;
                       max-width:750px;
                       background:#ffffff;
                       border-radius:12px;
                       border:1px solid #e0e0e0;
                       overflow:hidden;
                   ">

                    {{-- HEADER --}}
                    <tr>
                        <td style="background:{{ $primaryColor ?? '#3AB3AA' }};padding:30px;text-align:center;">

                            <div style="color:#ffffff; font-size:28px; font-weight:bold;">
                                {{ $appName ?? config('app.name') }}
                            </div>

                            @if(!empty($headerTitle))
                            <div style="
                                color:#ffffff;
                                font-size:14px;
                                margin-top:8px;
                            ">
                                {{ $headerTitle }}
                            </div>
                            @endif

                        </td>
                    </tr>


                    {{-- CONTENT --}}
                    <tr>
                        <td style="
                        padding:35px;
                        color:#555555;
                        font-size:15px;
                        line-height:1.7;
                    ">

                            {!! $content !!}

                        </td>
                    </tr>


                    {{-- FOOTER --}}
                    <tr>
                        <td style="
                        background:#f8fafb;
                        border-top:1px solid #eeeeee;
                        padding:25px 35px;
                        text-align:center;
                    ">

                            <p style="margin:0 0 15px; color:{{ $secondaryColor ?? '#372E14' }}; font-size:14px; font-weight:bold;">
                                {{ $footerName ?? "L'équipe " . ($appName ?? config('app.name')) }}
                            </p>

                            @if(!empty($supportEmail))
                            <p style="
                                margin:0 0 10px;
                                font-size:12px;
                                color:#888888;
                            ">
                                Support :
                                <a href="mailto:{{ $supportEmail }}"
                                    style="color:{{ $primaryColor ?? '#3AB3AA' }};">
                                    {{ $supportEmail }}
                                </a>
                            </p>
                            @endif

                            <p style="
                            margin:0;
                            color:#aaaaaa;
                            font-size:11px;
                        ">
                                {{ $footerText ?? 'Cet e-mail a été envoyé automatiquement.' }}
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>

    </table>

</body>

</html>