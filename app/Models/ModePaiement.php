<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModePaiement extends Model
{
    use HasFactory;
    protected $table = 'mode_Paiements';
    protected $primaryKey = 'numModePaiement';
    protected $fillable = [
        'nomModePaiement',
        'actif',
        'config',
        'image',
        'typePaiement'
    ];

    
    protected $casts = [
        'actif' => 'boolean',
        'config' => 'array', 
     ];
}