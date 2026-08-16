<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancelLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $url,
        public array $strings,
        public string $lang,
    ) {
        $this->locale($lang);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->strings['mail_cancel_subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cancel-link',
            with: [
                'url' => $this->url,
                'strings' => $this->strings,
            ],
        );
    }
}
