<?php

namespace App\Mail;

use App\Models\Commande;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommandeAnnuleeStockMail extends Mailable
{
    use Queueable, SerializesModels;

    public Commande $commande;
    public bool $etaitPaye;
    public array $produitsManquants;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Commande $commande,
        bool $etaitPaye = false,
        array $produitsManquants = []
    ) {
        $this->commande = $commande;
        $this->etaitPaye = $etaitPaye;
        $this->produitsManquants = $produitsManquants;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Annulation de votre commande #' . $this->commande->referenceCommande,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.commande-annulee-stock',
            with: [
                'commande' => $this->commande,
                'client' => $this->commande->utilisateur,
                'etaitPaye' => $this->etaitPaye,
                'produitsManquants' => $this->produitsManquants,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}