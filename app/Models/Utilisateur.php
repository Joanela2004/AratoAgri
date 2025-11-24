<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'numUtilisateur';
    protected $fillable = ['nomUtilisateur','email','contact','motDePasse','role','image'];
protected $hidden = ['motDePasse','remember_token'];


    public function getAuthPassword()
    {
        return $this->motDePasse;
    }

    public function setMotDePasseAttribute($value)
    {
        $this->attributes['motDePasse'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

   

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'numUtilisateur');
    }
}