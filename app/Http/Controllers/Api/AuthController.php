<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Utilisateur;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // Nécessaire pour gérer les fichiers

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Correction de la validation des types de fichiers (mime) et de la taille maximale
        $validatedData = $request->validate([
            'nomUtilisateur' => 'required',
            'email' => 'required|email|unique:utilisateurs,email',
            'contact' => 'required',
            'motDePasse' => 'required|string|confirmed', // Ajout de 'confirmed' pour la sécurité
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048' // Validation correcte
        ]);

        $user = new Utilisateur();
        $user->nomUtilisateur = $validatedData['nomUtilisateur'];
        $user->email = $validatedData['email'];
        $user->contact = $validatedData['contact'];
        $user->motDePasse = Hash::make($validatedData['motDePasse']); // Utilisation de Hash::make
        $user->role = 'client';

        $imageFileName = null; // Initialisation du nom du fichier

        // 💡 GESTION DE L'IMAGE : Stockage du fichier sur le disque
        if ($request->hasFile('image')) {
            // Stockage dans le dossier 'profiles' à l'intérieur de 'public'
            $path = $request->file('image')->store('profiles', 'public');
            // On enregistre uniquement le nom du fichier (sans le chemin 'profiles/')
            $imageFileName = basename($path);
        }
        
        // Enregistrement du nom du fichier dans la BDD (sera null si aucune image n'est fournie)
        $user->image = $imageFileName;

        $user->save();

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
           'user' => [
                'id' => $user->numUtilisateur, // Assurez-vous que c'est le bon champ (numUtilisateur ou id)
                'nomUtilisateur' => $user->nomUtilisateur,
                'email' => $user->email,
                'contact' => $user->contact,
                'role' => $user->role,
                'image' => $user->image, // Contient maintenant le nom du fichier ou null
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'motDePasse' => 'required'
        ]);

        $user = Utilisateur::where('email', $request->email)->first();

        // 💡 CORRECTION: Utilisation de Hash::check() pour la vérification du mot de passe
        if (!$user || !Hash::check($request->motDePasse, $user->motDePasse)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->numUtilisateur, // Assurez-vous que c'est le bon champ (numUtilisateur ou id)
                'nomUtilisateur' => $user->nomUtilisateur,
                'email' => $user->email,
                'contact' => $user->contact,
                'role' => $user->role,
                'image' => $user->image, // Retourne le nom du fichier ou null
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }
    
    public function logout(Request $request)
    {
        // Supprime le jeton actuel
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie.'], 200);
    }

    public function changePassword(Request $request)
    {
        // ... (votre code existant, inchangé)
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ]);

        if (!Hash::check($request->current_password, $user->motDePasse)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect'], 400);
        }

        $user->motDePasse = Hash::make($request->new_password);
        $user->save();

        $user->tokens()->delete();

        return response()->json(['message' => 'Mot de passe mis à jour, veuillez vous reconnecter.'], 200);
    }
}