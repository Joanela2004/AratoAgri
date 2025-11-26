<?php
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\PromoMail;

class SendPromoEmailJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $promotions;
    public $user;

    public function __construct($user, $promotions)
    {
        $this->user = $user;
        $this->promotions = $promotions;
    }

    public function handle()
    {
        Mail::to($this->user->email)->send(new PromoMail($this->promotions));
    }
}
