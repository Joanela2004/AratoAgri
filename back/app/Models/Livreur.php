<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // ← MODIFIÉ : Import pour l'authentification
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // ← AJOUTÉ : Si tu utilises Sanctum pour les tokens API

class Livreur extends Authenticatable // ← MODIFIÉ : Étend Authenticatable au lieu de Model
{
    use HasApiTokens, HasFactory, Notifiable; // ← AJOUTÉ : Intégration des traits de sécurité

    protected $table = 'livreurs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'telephone',
        'email',
        'password', // ← AJOUTÉ : Permet l'enregistrement en base
        'statut',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password', // ← AJOUTÉ : Empêche Laravel de renvoyer le mot de passe (même haché) dans le JSON
        'remember_token',
    ];
}