<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageContent;
    public $subjectText;

    public function __construct($subjectText, $messageContent)
    {
        $this->subjectText    = $subjectText;
        $this->messageContent = $messageContent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('alerte@aej-ci.net', 'Informations SIM'),
            subject: $this->subjectText,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.send_mail',
            with: [
                'messageContent' => $this->messageContent,
                'subjectText'    => $this->subjectText,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}