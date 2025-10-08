<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $primaryKey='numCommande';
    protected $fillable=['dateCommande','statut','montantTotal','adresseDeLivraison','numUtilisateur','numModePaiement'];

    //Relation avec client
    public function utilisateur(){
        return $this->belongsTo(Utilisateur::class,'numUtilisateur');
    }

    
    //une commande a plusieurs detailCommande
    public function detailCommandes(){
        return $this->hasMany(DetailCommande::class,'numCommande');
    }
    
    //une commande utilise un seul paiement
    public function paiement(){
        return $this->hasOne(Paiement::class,'numCommande');
        }

    //une commande possede une livraison
    public function livraison(){
        return $this->hasOne(Livraison::class,'numCommande');
    }
    //une commande appartient a un mode paiement
    public function mode_paiement(){
        return $this->belongsTo(ModePaiement::class,'numModePaiement');
    }
}
