<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
protected $primaryKey='numLivraison';
protected $fillable=['numCommande',
        'transporteur',
        'referenceColis',
        'contactTransporteur',
        'lieuLivraison',
        'dateExpedition',
        'dateLivraison',
        'statutLivraison',
    'fraisLivraison'];
public function commande()
{
return $this->belongsTo(Commande::class,'numCommande');
}

}
