<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
    protected $primaryKey = 'numLivraison';
    protected $fillable = [
        'numCommande',
        'lieuLivraison',
        'transporteur',
        'referenceColis',
        'fraisLivraison',
        'contactTransporteur',
        'dateExpedition',
        'dateLivraison',
        'statutLivraison'
    ];

    // Livraison liée à une commande
    public function commande()
    {
        return $this->belongsTo(Commande::class, 'numCommande');
    }
}
