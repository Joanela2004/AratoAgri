<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;
    protected $primaryKey ='numProduit';
    protected $fillable=['nomProduit','prix','poids','quantiteStock','image','numPromotion'];
    public function produit(){
        return $this->belongsTo(Categorie::class,'numCategorie');
    }

    public function detailPanier(){
        return $this->hasOne(DetailPanier::class,'numProduit');
    }

    //un produit peut etre dans plusieurs commandes
    public function commandes(){
        return $this->belongsToMany(Commande::class,'detailCommande','numProduit','numCommande')->withPivot('quantite','prixUnitaire')->withTimestamps();
    }

    // Un produit peut avoir une promo (ou pas)
    public function promotion() {
        return $this->belongsTo(Promotion::class, 'numPromotion');
    }
}
