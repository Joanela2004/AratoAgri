<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $primaryKey='numCommande';
    protected $fillable=['dateCommande','statut','montantTotal','adresseDeLivraison','numUtilisateur'];

    //Relation avec client
    public function utilisateur(){
        return $this->belongsTo(Utilisateur::class,'numUtilisateur');
    }

    //Relation avec les produits
    public function produits(){
        return $this->belongsToMany(Produit::class,'detailCommande')->withPivot('quantite','PrixUnitaire')->withTimestamps();
    }
    
    //une commande utilise un seul paiement
    public function paiement(){
        return $this->hasOne(Paiement::class,'numCommande');
        }

    //une commande possede une livraison
    public function livraison(){
        return $this->hasOne(Livraison::class,'numCommande');
    }
}
