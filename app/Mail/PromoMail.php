<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class PromoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $promo;

    public function __construct($promo)
    {
        $this->promo = $promo;
    }

    public function build()
    {
        return $this->subject('Votre code promo exclusif !')
                    ->view('emails.promotions')
                    ->with([
                        'promo' => $this->promo
                    ]);
    }
}
