<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $htmlBody,
        public string $version,
        public string $unsubscribeUrl,
        public array $strings,
        public string $lang,
    ) {
        $this->locale($lang);

        // RFC 8058 one-click unsubscribe headers (Gmail/Outlook auto-detection).
        $this->withSymfonyMessage(function (Email $message) {
            $message->getHeaders()
                ->addTextHeader('List-Unsubscribe', "<{$this->unsubscribeUrl}>")
                ->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        });
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: strtr($this->strings['mail_newsletter_subject'], ['{version}' => $this->version]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
            with: [
                'htmlBody' => $this->htmlBody,
                'unsubscribeUrl' => $this->unsubscribeUrl,
                'strings' => $this->strings,
            ],
        );
    }
}
