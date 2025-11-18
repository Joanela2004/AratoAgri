<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $primaryKey = 'numCommande';

    protected $fillable = [
        'numUtilisateur',
        'numModePaiement',
        'numLieu',
        'statut',
        'sousTotal',
        'fraisLivraison',
        'montantTotal',
        'payerLivraison',
        'codePromo',
        'dateCommande'
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'numUtilisateur');
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'numModePaiement');
    }

    public function lieu()
    {
        return $this->belongsTo(Lieu::class, 'numLieu');
    }

    public function livraisons()
    {
        return $this->hasOne(Livraison::class, 'numCommande');
    }

    public function detailCommandes()
    {
        return $this->hasMany(DetailCommande::class, 'numCommande');
    }
}
