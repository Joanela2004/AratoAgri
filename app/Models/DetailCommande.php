<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailCommande extends Model
{
protected $primaryKey ='numDetailCommande';
protected $table='detail_commandes';
protected $fillable=['numCommande','numProduit','quantite','prixUnitaire'];

//un ou plusieurs detailCommande appartient a un commande
public function commande(){
    return $this->belongsTo(Commande::class,'numCommande');
}
//un detailCommande appartient un produit
public function produit(){
    return $this->belongsTo(Produit::class,'numProduit');
}    
}
