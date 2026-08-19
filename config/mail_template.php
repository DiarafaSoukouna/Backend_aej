<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application
    |--------------------------------------------------------------------------
    */

    'app_name' => env('MAIL_FROM_NAME', 'AEJ'),

    /*
    |--------------------------------------------------------------------------
    | Colors
    |--------------------------------------------------------------------------
    */

    'primary_color' => env(
        'MAIL_PRIMARY_COLOR',
        '#3AB3AA'
    ),

    'secondary_color' => env(
        'MAIL_SECONDARY_COLOR',
        '#372E14'
    ),

    /*
    |--------------------------------------------------------------------------
    | Footer
    |--------------------------------------------------------------------------
    */

    'support_email' => env(
        'MAIL_SUPPORT_EMAIL',
        'support@example.com'
    ),

    'footer_name' => env(
        'MAIL_FOOTER_NAME',
        "L'équipe de support AEJ"
    ),

    'footer_text' => env(
        'MAIL_FOOTER_TEXT',
        'Cet e-mail a été envoyé automatiquement.'
    ),

];