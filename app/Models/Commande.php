<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $primaryKey = 'numCommande';
    protected $fillable = [
        'numUtilisateur',
        'numModePaiement',
        'dateCommande',
        'statut',
        'montantTotal',
        'adresseDeLivraison',
        'payerLivraison'
    ];

    // Relation avec l'utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'numUtilisateur');
    }

    // Une commande a plusieurs détails de commande
    public function detailCommandes()
    {
        return $this->hasMany(DetailCommande::class, 'numCommande');
    }

    // Une commande a un paiement ou plusieurs paiements (si frais livraison payé après)
    public function paiement()
    {
        return $this->hasMany(Paiement::class, 'numCommande');
    }

    // Une commande possède une livraison
    public function livraison()
    {
        return $this->hasOne(Livraison::class, 'numCommande');
    }

    // Une commande appartient à un mode de paiement
    public function mode_paiement()
    {
        return $this->belongsTo(ModePaiement::class, 'numModePaiement');
    }
}
