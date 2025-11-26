<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        if (!Utilisateur::where('email', 'admin@example.com')->exists()) {
        Utilisateur::create([
            'nomUtilisateur' => 'Admin',
            'email' => 'admin@example.com',     
            'motDePasse' => Hash::make('motdepasse123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
    }
}
