<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    protected $primaryKey ='numUtilisateur';
    protected $fillable=['nomUtilisateur','email','contact'];
//un utilisateur peut avoir plusieurs panier
    public function paniers(){
    return $this->hasMany(Panier::class,'numUtilisateur');
}
//un utilisateur  possedent un ou plusieurs commande
public function commandes(){
    return $this->hasMany(Commande::class);
}
}
