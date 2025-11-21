<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Decoupe extends Model
{
    use HasFactory;

    protected $table = 'decoupes';
    protected $primaryKey = 'numDecoupe';

    protected $fillable = [
        'nomDecoupe',
        'coefficient'
    ];

       public function detailCommandes()
    {
        return $this->hasMany(DetailCommande::class, 'numDecoupe', 'numDecoupe');
    }
    
}
