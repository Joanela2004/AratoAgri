<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
protected $primaryKey='numPromotion';
protected $fillable=['nomPromotion','valeur','dateDebut','dateFin','codePromo','statutPromotion'];
public function produits(){
    return $this->hasMany(Produit::class,'numPromotion')->withTimestamps();
}
}
