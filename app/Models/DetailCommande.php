<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailCommande extends Model
{
    use HasFactory;

    protected $table = 'detail_commandes';
    protected $primaryKey = 'numDetailCommande';

    protected $fillable = [
        'numCommande',
        'numProduit',
        'numDecoupe',
        'poids',
        'prixUnitaire',
        'sousTotal'
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class, 'numCommande', 'numCommande');
    }
public function produit()
{
    return $this->belongsTo(Produit::class, 'numProduit', 'numProduit')->withTrashed();
}


    public function decoupe()
    {
        return $this->belongsTo(Decoupe::class, 'numDecoupe', 'numDecoupe');
    }
}
