<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Promotion extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $primaryKey = 'numPromotion';
    protected $fillable = ['nomPromotion', 'automatique','valeur', 'typePromotion', 'dateDebut', 'dateFin', 'codePromo', 'statutPromotion', 'montantMinimum','numProduit'];
    protected $casts = [
        'dateDebut' => 'datetime',
        'dateFin'   => 'datetime',
        'statutPromotion'   => 'boolean',   
        'automatique'       => 'boolean',

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
public function getStatutAttribute(): string
    {
        if (!$this->statutPromotion) {
            return 'inactive';
        }

        $now = Carbon::now();

        if ($this->dateDebut && $now->lt($this->dateDebut)) {
            return 'en_attente';
        }

        if ($this->dateFin && $now->gt($this->dateFin)) {
            return 'expirée';
        }

        return 'active';
    }


}