<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PromoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $promotions;

    /**
     * Create a new message instance.
     */
    public function __construct($promotions)
    {
        $this->promotions = $promotions;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('🎁 Vos codes promo actifs')
                    ->markdown('emails.promotions')
                    ->with([
                        'promotions' => $this->promotions,
                    ]);
    }
}
