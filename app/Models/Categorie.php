<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $primaryKey='numcategorie';
    protected $fillable=['nomCategorie'];
   //une categorie contiennent plusieurs produits
    public function produits(){
        return $this->hasMany(Produit::class,'numCategorie');   }

}
