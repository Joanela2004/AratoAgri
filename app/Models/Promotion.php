<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $primaryKey = 'numPromotion';
    protected $fillable = ['nomPromotion', 'valeur', 'typePromotion', 'dateDebut', 'dateFin', 'codePromo', 'statutPromotion', 'montantMinimum'];
    
    public function produits()
    {
        return $this->hasMany(Produit::class, 'numPromotion')->withTimestamps();
    }
}