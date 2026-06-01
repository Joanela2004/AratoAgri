<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livreur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthLivreurController extends Controller
{
    /**
     * Connexion du livreur et génération du token Sanctum.
     */
    public function login(Request $request)
    {
        // 1. Validation des données entrantes
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. Recherche du livreur par son email
        $livreur = Livreur::where('email', $request->email)->first();

        // 3. Vérification de l'existence et du mot de passe haché
        if (!$livreur || !Hash::check($request->password, $livreur->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects.'
            ], 401);
        }

        // 4. Génération du token d'accès via Sanctum
        // On lui donne une portée d'aptitude "role:livreur" par sécurité
        $token = $livreur->createToken('livreur_auth_token', ['role:livreur'])->plainTextToken;

        // 5. Réponse envoyée au frontend React
        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'token' => $token,
            'livreur' => [
                'id' => $livreur->id,
                'nom' => $livreur->nom,
                'email' => $livreur->email,
                'telephone' => $livreur->telephone,
                'statut' => $livreur->statut,
            ]
        ], 200);
    }

    /**
     * Déconnexion du livreur (Révocation du token).
     */
    public function logout(Request $request)
    {
        // Supprime le token qui a servi à authentifier la requête actuelle
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie avec succès'
        ], 200);
    }
}