<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Utilisateur;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
  public function register(Request $request)
    {
        $validatedData = $request->validate([
            'nomUtilisateur' => 'required',
            'email' => 'required|email|unique:utilisateurs,email',
            'contact' => 'required',
            'motDePasse' => 'required|string',
        ]);

        $user = new Utilisateur();
        $user->nomUtilisateur = $validatedData['nomUtilisateur'];
        $user->email = $validatedData['email'];
        $user->contact = $validatedData['contact'];
        $user->motDePasse = bcrypt($validatedData['motDePasse']);
        $user->role = 'client'; // Définir le rôle par défaut
        
        $user->save();

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'user' => $user, // Retourner l'objet utilisateur
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'motDePasse' => 'required'
        ]);

        $user = Utilisateur::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->motDePasse, $user->motDePasse)) {
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