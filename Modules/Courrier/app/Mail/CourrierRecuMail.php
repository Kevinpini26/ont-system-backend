<?php

namespace Modules\Courrier\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Courrier\Models\Courrier;

/**
 * Envoyée à la direction destinataire d'un nouveau courrier. Toujours
 * dispatchée via Mail::queue() (jamais ->send()) : voir
 * CourrierCircuitService::creer().
 */
class CourrierRecuMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Courrier $courrier) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nouveau courrier reçu — {$this->courrier->numero_accuse_reception}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'courrier::emails.recu');
    }
}
