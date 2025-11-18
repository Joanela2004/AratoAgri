<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailCommande extends Model
{
    protected $fillable = [
        'numCommande',
        'numProduit',
        'poids',
        'decoupe',
        'prixUnitaire',
        'sousTotal'
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class, 'numCommande', 'numCommande');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'numProduit', 'numProduit');
    }
}
