<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $primaryKey = 'numCommande';

    protected $fillable = [
        'numUtilisateur', 'numModePaiement', 'numLieu', 'statut', 'sousTotal',
        'fraisLivraison', 'montantTotal', 'payerLivraison', 'codePromo', 
        'numPromotion', 'dateCommande','estConsulte'
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'numUtilisateur', 'numUtilisateur');
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'numModePaiement', 'numModePaiement');
    }

    public function lieu()
    {
        return $this->belongsTo(LieuLivraison::class, 'numLieu', 'numLieu');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'numPromotion', 'numPromotion');
    }
    public function detailCommandes()
    {
        return $this->hasMany(DetailCommande::class, 'numCommande', 'numCommande');
    }
    public function livraisons()
    {
            return $this->hasMany(Livraison::class, 'numCommande', 'numCommande');
    }
}
