<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $primaryKey = 'numPaiement';
    protected $fillable = [
        'numCommande',
        'numModePaiement',
        'montantApayer',
        'statut',
        'datePaiement'
    ];

    // Paiement lié à une commande
    public function commande()
    {
        return $this->belongsTo(Commande::class, 'numCommande');
    }

    // Paiement réalisé via un mode de paiement
    public function mode_paiement()
    {
        return $this->belongsTo(ModePaiement::class, 'numModePaiement');
    }
}
