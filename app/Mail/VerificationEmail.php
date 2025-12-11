<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Utilisateur;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(Utilisateur $user)
    {
        $this->user = $user;
    }

    public function build()
    {
       $verificationUrl = route('verify.email', $this->user->email_verification_token);
        return $this->subject('Confirmez votre email')
                    ->view('emails.verify')
                    ->with(['url' => $verificationUrl]);
    }
}
