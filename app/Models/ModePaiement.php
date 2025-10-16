<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModePaiement extends Model
{
    protected $table='mode_paiements';
    protected $primaryKey='numModePaiement';
    protected $fillable=['nomModePaiement','solde'];
    
    public function paiements(){
        return $this->hasMany(Paiement::class,'numModePaiement');
    }
}
