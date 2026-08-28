<?php

namespace App\Services;

use App\Models\Configuration;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    protected ?array $smtpConfig = null;

    public function __construct()
    {
        $configuration = Configuration::first();

        if ($configuration) {
            $this->smtpConfig = [
                'host' => $configuration->smtp_host_notifications,
                'port' => $configuration->smtp_port_notifications,
                'encryption' => $configuration->smtp_encrypt_notifications,
                'username' => $configuration->email_notifications,
                'password' => $configuration->mot_de_passe_email_notifications,
                'from' => [
                    'address' => $configuration->email_notifications,
                    'name' => $configuration->sigle_systeme ?? 'AEJ'
                ]
            ];

            Log::info('MailService initialized with database configuration', [
                'host' => $this->smtpConfig['host'],
                'port' => $this->smtpConfig['port'],
                'from' => $this->smtpConfig['from']['address']
            ]);
        }
    }

    /**
     * Génère le layout principal des emails.
     */
    protected function render(string $content, ?string $subject = null, array $options = []): string
    {
        return view('emails.layout', [
            'subject' => $subject,
            'content' => $content,
            'appName' => config('mail_template.app_name'),
            'primaryColor' => config('mail_template.primary_color'),
            'secondaryColor' => config('mail_template.secondary_color'),
            'supportEmail' => config('mail_template.support_email'),
            'footerName' => config('mail_template.footer_name'),
            'footerText' => config('mail_template.footer_text'),
            'headerTitle' => $options['headerTitle'] ?? null,
        ])->render();
    }

    /**
     * Envoyer un email.
     */
    protected function send(string $email, string $subject, string $content, array $options = [])
    {
        $html = $this->render(
            $content,
            $subject,
            $options
        );

        config([
            'mail.mailers.smtp.host' => $this->smtpConfig['host'],
            'mail.mailers.smtp.port' => $this->smtpConfig['port'],
            'mail.mailers.smtp.encryption' => $this->smtpConfig['encryption'],
            'mail.mailers.smtp.username' => $this->smtpConfig['username'],
            'mail.mailers.smtp.password' => $this->smtpConfig['password'],
            'mail.from.address' => $this->smtpConfig['from']['address'],
            'mail.from.name' => $this->smtpConfig['from']['name'],
        ]);

        return Mail::html(
            $html,
            function ($message) use ($email, $subject) {
                $message
                    ->to($email)
                    ->subject($subject);
            }
        );
    }

    /**
     * Email de bienvenue.
     */
    public function sendWelcomeEmail($personnel)
    {
        $content = view(
            'emails.welcome',
            [
                'personnel' => $personnel,
            ]
        )->render();

        return $this->send(
            $personnel->email,
            'Bienvenue sur AEJ – Votre compte a été créé',
            $content,
            [
                'headerTitle' => 'Création de votre compte',
            ]
        );
    }

    /**
     * Email de configuration de compte (token SETUP).
     */
    public function sendSetupEmail($personnel, string $setupUrl)
    {
        $content = view(
            'emails.setup',
            [
                'personnel' => $personnel,
                'setupUrl' => $setupUrl,
            ]
        )->render();

        return $this->send(
            $personnel->email,
            'Configuration de votre compte AEJ',
            $content,
            [
                'headerTitle' => 'Configuration de votre compte',
            ]
        );
    }

    /**
     * Email OTP.
     */
    public function sendOtpEmail(string $email, string $otp)
    {
        $content = view(
            'emails.otp',
            ['otp' => $otp,]
        )->render();

        return $this->send($email, 'Votre code de vérification AEJ', $content, [
            'headerTitle' => 'Vérification de votre compte',
        ]);
    }

    /**
     * Réinitialisation du mot de passe.
     */
    public function sendPasswordResetEmail(string $email, string $resetUrl)
    {
        $content = view('emails.password-reset', ['resetUrl' => $resetUrl])->render();

        return $this->send($email, 'Réinitialisation de votre mot de passe', $content, [
            'headerTitle' => 'Sécurité du compte',
        ]);
    }

    /**
     * Alerte de sécurité.
     */
    public function sendSecurityAlert(string $email, string $message, ?string $actionUrl = null)
    {
        $content = view('emails.account-alert', ['message' => $message, 'actionUrl' => $actionUrl])->render();

        return $this->send($email, 'Alerte de sécurité – AEJ', $content, [
            'headerTitle' => 'Alerte de sécurité',
        ]);
    }
}
