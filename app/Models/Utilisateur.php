<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
class Utilisateur extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'numUtilisateur'; // si tu as une clé personnalisée
    protected $table = 'utilisateurs';

    protected $fillable = [
        'nomUtilisateur',
        'email',
        'contact',
        'motDePasse',
        'role',
        'image',
        'code_reinitialisation',
    'code_reinitialisation_expire_le',
        'email_verification_token',
        'email_verified_at',
    ];

    protected $hidden = [
        'motDePasse',
        'email_verification_token',
    ];

    

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'numUtilisateur');
    }
public function promotionsRecues()
{
    return $this->belongsToMany(Promotion::class, 'promotion_utilisateur', 'numUtilisateur', 'numPromotion')
                ->withPivot('code_envoye', 'date_expiration', 'statut')
                ->withTimestamps();
}

}
