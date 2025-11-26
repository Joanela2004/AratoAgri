<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPanier extends Model
{
    use HasFactory;

    protected $primaryKey = 'numDetailPanier';
    protected $fillable = ['numPanier', 'numProduit', 'poids', 'prixUnitaire', 'sousTotal', 'numDecoupe'];

    // Relation avec le panier
    public function panier()
    {
        return $this->belongsTo(Panier::class, 'numPanier', 'numPanier');
    }
    public function produit() {
    return $this->belongsTo(Produit::class, 'numProduit', 'numProduit');
    }
}
