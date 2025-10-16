<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Panier extends Model
{
    protected $primaryKey='numPanier';
    protected $fillable=['dateCreation','numUtilisateur'];
   
    //un panier appartient a un utilisateur
    public function utilisateur(){
    return $this->belongsTo(Utilisateur::class,'numUtilisateur');
    }
    
    //un panier a plusieurs detail panier
    public function detailPaniers(){
        return $this->hasMany(DetailPanier::class,'numPanier');
    }

   
}
