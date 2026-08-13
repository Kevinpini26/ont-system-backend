<?php

namespace Modules\Courrier\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Courrier\Models\Courrier;

class AvisDgEnAttenteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Courrier $courrier) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Avis en attente depuis plus de 48h — {$this->courrier->numero_accuse_reception}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'courrier::emails.avis-dg-en-attente');
    }
}
