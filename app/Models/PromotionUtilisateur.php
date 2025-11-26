<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionUtilisateur extends Model
{
    use HasFactory;

    protected $table = 'promotion_utilisateur';

    protected $primaryKey = 'numPromotion_Utilisateur';

    protected $fillable = [
        'numPromotion',
        'numUtilisateur',
        'statut',
        'dateExpiration',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'numPromotion', 'numPromotion');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'numUtilisateur', 'numUtilisateur');
    }
}

