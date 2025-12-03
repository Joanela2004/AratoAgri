<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produit extends Model
{
    use HasFactory;

    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $primaryKey = 'numProduit';
    protected $fillable = [
        'nomProduit',
        'prix',
        'poids',  
        'image',
        'numCategorie',
        'numPromotion',
        'cout'
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'numCategorie');
    }

    public function detailCommandes()
    {
        return $this->hasMany(DetailCommande::class, 'numProduit', 'numProduit');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'numPromotion');
    }
}
