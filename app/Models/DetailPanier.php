<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPanier extends Model
{
   protected $table='detail_paniers';
   protected $primaryKey='numDetailPanier';
   protected $fillable=['poids','decoupe','numProduit','numPanier'] ;
   
   //un detail panier ou plusieurs appartient a un panier 
   public function panier(){
    return $this->belongsTo(Panier::class,'numPanier');
   }
   //un detail panier appartient un produit
   public function produit(){
    return $this->belongsTo(Produit::class,'numProduit');
   }
}
