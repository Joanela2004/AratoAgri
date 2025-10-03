<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $primaryKey='numPaiement';
    protected $fillable=['numCommande','numModePaiement','statut','datePaiement'];
    
    
    //un paiement appartient a une commande
    public function commande(){
        return $this->belongsTo(Commande::class,'numCommande');
    }
    
    //un paiement utilise un mode de paiement
    public function modePaiement(){
        return $this->belongsTo(ModePaiement::class,'numModePaiement');
    }
}
