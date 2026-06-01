<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LieuLivraison extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
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
