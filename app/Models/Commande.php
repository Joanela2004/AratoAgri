<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $primaryKey = 'numCommande';

    protected $fillable = [
        'numUtilisateur',
        'numModePaiement',
        'numLieu',
        'numPromotion',
        'codePromo',
        'statut',
        'sousTotal',
        'fraisLivraison',
        'montantTotal',
        'payerLivraison',
        'dateCommande',
        'nomPayeur',
        'carteDerniers',
        'paypalEmail',
        'numeroPayeur'
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'numUtilisateur', 'numUtilisateur');
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'numModePaiement', 'numModePaiement');
    }

    public function lieu()
    {
        return $this->belongsTo(LieuLivraison::class, 'numLieu', 'numLieu');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'numPromotion', 'numPromotion');
    }
}
