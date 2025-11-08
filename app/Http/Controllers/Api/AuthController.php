<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nom_utilisateur' => 'required|string|max:100',
            'email' => 'required|email|unique:utilisateurs,email',
            'contact' => 'required|string|max:15',
            'mot_de_passe' => 'required|string|min:6|confirmed',
        ]);

        $utilisateur = Utilisateur::create([
            'nomUtilisateur' => $request->nom_utilisateur,
            'email' => $request->email,
            'contact' => $request->contact,
            'motDePasse' => Hash::make($request->mot_de_passe),
            'role' => 'client'
        ]);

        $token = $utilisateur->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $utilisateur,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mot_de_passe' => 'required'
        ]);

        $user = Utilisateur::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->mot_de_passe, $user->motDePasse)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }
    public function changePassword(Request $request)
{
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