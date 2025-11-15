<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;
    protected $primaryKey ='numProduit';
    protected $fillable=['nomProduit','prix','poids','quantiteStock','image','numCategorie','numPromotion'];
    
    public function categorie(){
        return $this->belongsTo(Categorie::class,'numCategorie');
    }

   

    public function detailCommande(){
        return $this->hasOne(DetailCommande::class,'numProduit');
    }

    // Un produit peut avoir une promo (ou pas)
    public function promotion() {
        return $this->belongsTo(Promotion::class, 'numPromotion');
    }
}
