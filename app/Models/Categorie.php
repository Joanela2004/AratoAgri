<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $primaryKey='numCategorie';
    protected $fillable=['nomCategorie'];
   
       public function produits(){
        return $this->hasMany(Produit::class,'numCategorie');   
    }

}
