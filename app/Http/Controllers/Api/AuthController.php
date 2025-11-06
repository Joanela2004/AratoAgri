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
    $request->validate([
        'nomUtilisateur' => 'required|string|max:100',
        'email' => 'required|email|unique:utilisateurs,email',
        'contact' => 'required|string|min:10|max:10',
        'motDePasse' => 'required|string|min:6',
        'role' => 'required|in:admin,client'
    ]);

    $utilisateur = Utilisateur::create([
        'nomUtilisateur' => $request->nomUtilisateur,
        'email' => $request->email,
        'contact' => $request->contact,
        'motDePasse' => Hash::make($request->motDePasse),
        'role' => $request->role
    ]);

    $token = $utilisateur->createToken('token')->plainTextToken;

    return response()->json([
        'message' => 'Inscription réussie',
        'utilisateur' => $utilisateur,
        'token' => $token
    ], 201);
}

public function logout(Request $request)
{
    $request->user()->tokens()->delete();
    return response()->json(['message' => 'Déconnexion réussie'], 200);
}


    public function login(Request $request){
        $request->validate([
            'email'=>'required|email',
            'motDePasse'=>'required|string',
        ]);

        $utilisateur=Utilisateur::where('email',$request->email)->first();
        if(!$utilisateur || !Hash::check($request->motDePasse, $utilisateur->motDePasse)){
            return response()->json(['error'=>'identifiants invalides'],401);
        }

        $token = $utilisateur->createToken('token')->plainTextToken;
        return response()->json([
            'message'=>'Connexion réussie',
            'token'=>$token,
            'utilisateur'=>$utilisateur
        ],200);
    }


}
