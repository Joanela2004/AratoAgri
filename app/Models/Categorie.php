<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $primaryKey='numCategorie';
    protected $fillable=['nomCategorie'];
   
       public function produits(){
        return $this->hasMany(Produit::class,'numCategorie');   
    }

}
