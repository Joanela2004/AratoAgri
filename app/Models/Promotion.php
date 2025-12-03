<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $primaryKey = 'numPromotion';
    protected $fillable = ['nomPromotion', 'valeur', 'typePromotion', 'dateDebut', 'dateFin', 'codePromo', 'statutPromotion', 'montantMinimum','numProduit'];
    protected $casts = [
        'dateDebut' => 'date',
        'dateFin'   => 'date',

    ];
    public function produits()
    {
        return $this->hasMany(Produit::class, 'numPromotion')->withTimestamps();
    }
public function produitCible()
    {
        return $this->belongsTo(Produit::class, 'numProduit');
    }
public function utilisateurs()
{
    return $this->belongsToMany(Utilisateur::class, 'promotion_utilisateur', 'numPromotion', 'numUtilisateur')
                ->withPivot('code_envoye', 'date_expiration', 'statut')
                ->withTimestamps();
}

}