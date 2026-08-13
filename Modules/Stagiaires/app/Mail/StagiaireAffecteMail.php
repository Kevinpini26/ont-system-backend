<?php

namespace Modules\Stagiaires\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Stagiaires\Models\Stagiaire;

/**
 * Toujours dispatchée via Mail::queue() (jamais ->send()) : voir
 * StagiaireCircuitService::affecter(). Vient s'ajouter à la notification
 * interne (StagiaireAffecteNotification, canal "database") déjà en place —
 * l'un alimente la cloche de notifications, l'autre la boîte mail.
 */
class StagiaireAffecteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Stagiaire $stagiaire) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Stagiaire affecté à votre direction — {$this->stagiaire->nom}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'stagiaires::emails.affecte');
    }
}
