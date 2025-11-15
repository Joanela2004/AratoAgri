<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailCommande extends Model
{
    protected $primaryKey = 'numDetailCommande';
    protected $table = 'detail_commandes';
    protected $fillable = [
        'numCommande',
        'numProduit',
        'poids',
        'decoupe',
        'prixUnitaire',
        'sousTotal'
    ];

    // Appartient à une commande
    public function commande()
    {
        return $this->belongsTo(Commande::class, 'numCommande');
    }

    // Appartient à un produit
    public function produit()
    {
        return $this->belongsTo(Produit::class, 'numProduit');
    }
}
