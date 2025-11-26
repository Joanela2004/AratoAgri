<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panier extends Model
{
    use HasFactory;

    protected $primaryKey = 'numPanier'; // clé primaire non standard
    protected $fillable = ['numUtilisateur'];

    // Relation avec les détails du panier
    public function detailsPaniers()
    {
        return $this->hasMany(DetailPanier::class, 'numPanier', 'numPanier');
    }
}
