<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $table = 'utilisateurs';
    protected $primaryKey ='numUtilisateur';
    protected $fillable=['nomUtilisateur','email','contact','motDePasse','role'];
    protected $hidden =['motDePasse','souvenirToken'];

    public function getAuthPassword(){
        return $this->motDePasse;
    }
    // hachage de mot de passe
    public function setMotDePasseAttribute($value){
        $this->attributes['motDePasse']=Hash::make($value);
    }
//un utilisateur peut avoir plusieurs panier
    public function paniers(){
    return $this->hasMany(Panier::class,'numUtilisateur');
}
//un utilisateur  possedent un ou plusieurs commande
public function commandes(){
    return $this->hasMany(Commande::class,'numUtilisateur');
}
}
