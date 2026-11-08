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

        if ($this->smtpConfig) {
            config([
                'mail.mailers.smtp.host' => $this->smtpConfig['host'],
                'mail.mailers.smtp.port' => $this->smtpConfig['port'],
                'mail.mailers.smtp.encryption' => $this->smtpConfig['encryption'],
                'mail.mailers.smtp.username' => $this->smtpConfig['username'],
                'mail.mailers.smtp.password' => $this->smtpConfig['password'],
                'mail.from.address' => $this->smtpConfig['from']['address'],
                'mail.from.name' => $this->smtpConfig['from']['name'],
            ]);
        }

        return Mail::html(
            $html,
            function ($message) use ($email, $subject, $options) {
                $message
                    ->to($email)
                    ->subject($subject);

                if (!empty($options['attachments'])) {
                    foreach ($options['attachments'] as $attachment) {
                        if (is_array($attachment)) {
                            if (!empty($attachment['path']) && file_exists($attachment['path'])) {
                                $message->attach($attachment['path'], [
                                    'as' => $attachment['as'] ?? basename($attachment['path']),
                                    'mime' => $attachment['mime'] ?? 'application/pdf',
                                ]);
                            } elseif (!empty($attachment['data'])) {
                                $message->attachData(
                                    $attachment['data'],
                                    $attachment['as'] ?? 'document.pdf',
                                    ['mime' => $attachment['mime'] ?? 'application/pdf']
                                );
                            }
                        } elseif (is_string($attachment) && file_exists($attachment)) {
                            $message->attach($attachment);
                        }
                    }
                }
            }
        );
    }






    /**
     * ###############################################################
     * ###############################################################
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
     * ###############################################################
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
     * ###############################################################
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
     * ###############################################################
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
     * ###############################################################
     * Alerte de sécurité.
     */
    public function sendSecurityAlert(string $email, string $message, ?string $actionUrl = null)
    {
        $content = view('emails.account-alert', [
            'message' => $message,
            'actionUrl' => $actionUrl,
        ])->render();

        return $this->send($email, 'Alerte de sécurité – AEJ', $content, [
            'headerTitle' => 'Alerte de sécurité',
        ]);
    }

    /**
     * ###############################################################
     * Rappel de paiement de garantie.
     */
    public function sendGarantieRappelEmail(string $email, array $garantieData)
    {
        $content = view('emails.garantie-rappel', [
            'montant' => $garantieData['montant'],
            'date_rappel' => $garantieData['date_rappel'],
            'statut' => $garantieData['statut'],
            'micro_projet_code' => $garantieData['micro_projet_code'],
            'organisme' => $garantieData['organisme'],
        ])->render();

        return $this->send($email, 'Rappel de paiement de garantie', $content, [
            'headerTitle' => 'Rappel de paiement',
        ]);
    }

     /**
     * ###############################################################
     * Alerte au promoteur pour ajout d'un projet.
     */
    public function sendProjetAlertEmail(string $email, array $projetData)
    {
        $content = view('emails.projet-alert', [
            'intitule' => $projetData['intitule'],
            'matricule' => $projetData['matricule'],
            'montant' => $projetData['montant_total'],
        ])->render();

        return $this->send($email, 'Alerte de projet – AEJ', $content, [
            'headerTitle' => 'Alerte de projet',
        ]);
    }

    /**
     * ###############################################################
     * Alerte etape du workflow
     */
    public function sendWorkflowAlertEmail(string $email, array $workflowData)
    {
        $content = view('emails.workflow-alert', [
            'etape' => $workflowData['etape'],
            'projet' => $workflowData['projet'],
            'action' => $workflowData['action'],
        ])->render();

        return $this->send($email, 'Alerte étape du workflow – AEJ', $content, [
            'headerTitle' => 'Alerte étape du workflow',
        ]);
    }

    /**
     * ###############################################################
     * Rappel formel d'échéances impayées au promoteur avec rapport PDF joint.
     */
    public function sendRappelRemboursementEmail(string $email, array $data, ?string $pdfPath = null)
    {
        $content = view('emails.rappel-remboursement', [
            'promoteur'     => $data['promoteur'],
            'projet'        => $data['projet'],
            'echeances'     => $data['echeances'],
            'total_impaye'  => $data['total_impaye'],
            'numero_rappel' => $data['numero_rappel'],
            'max_rappels'   => $data['max_rappels'] ?? 5,
            'date_rappel'   => $data['date_rappel'] ?? now(),
        ])->render();

        $intituleProjet = is_array($data['projet']) 
            ? ($data['projet']['intitule'] ?? ($data['projet']['code'] ?? 'Votre projet'))
            : ($data['projet']->intitule ?? ($data['projet']->code ?? 'Votre projet'));

        $codeProjet = is_array($data['projet'])
            ? ($data['projet']['code'] ?? 'PROJET')
            : ($data['projet']->code ?? 'PROJET');

        $subject = sprintf(
            'Rappel formel #%d/5 : Échéances de remboursement impayées – %s',
            $data['numero_rappel'],
            $intituleProjet
        );

        $options = [
            'headerTitle' => 'Rappel formel de remboursement',
        ];

        if ($pdfPath && file_exists($pdfPath)) {
            $options['attachments'] = [
                [
                    'path' => $pdfPath,
                    'as'   => 'Rapport_Situation_Remboursement_' . $codeProjet . '.pdf',
                    'mime' => 'application/pdf',
                ]
            ];
        }

        return $this->send($email, $subject, $content, $options);
    }
}
