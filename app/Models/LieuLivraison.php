<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LieuLivraison extends Model
{
    protected $table = 'lieux_livraison';
    protected $primaryKey = 'numLieu';
    protected $fillable = [
        'nomLieu',
        'fraisLieu'
    ];

    public function livraisons()
    {
        return $this->hasMany(Livraison::class, 'numLieu');
    }
}
