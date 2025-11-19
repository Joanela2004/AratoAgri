<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModePaiement extends Model
{
    use HasFactory;
    protected $table = 'modePaiements';
    protected $primaryKey = 'numModePaiement';
    protected $fillable = [
        'nomModePaiement',
        'actif',
        'config',
        'image',
    ];

    
    protected $casts = [
        'actif' => 'boolean',
        'config' => 'array', 
     ];
}