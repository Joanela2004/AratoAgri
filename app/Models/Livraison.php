<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
protected $primaryKey='numLivraison';
protected $fillable=['dateExpedition','dateLivraison','modeLivraison','statutLivraison','transporteur','numCommande'];
public function commande()
{
return $this->belongsTo(Commande::class,'numCommande');
}
}
