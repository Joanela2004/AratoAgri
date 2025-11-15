<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModePaiement extends Model
{
    use HasFactory;

    protected $table = 'mode_paiements';
    protected $primaryKey = 'numModePaiement';

    protected $fillable = [
        'nomModePaiement', 'solde',
        'numero_carte', 'nom_carte', 'expiration_carte', 'cvv',
        'nom_banque', 'numero_compte', 'iban', 'swift',
        'operateur_mobile', 'numero_mobile', 'nom_mobile',
        'paypal_email', 'paypal_id'
    ];

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'numModePaiement', 'numModePaiement');
    }
}
