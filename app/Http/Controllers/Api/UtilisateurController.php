<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;

class UtilisateurController extends Controller
{
    // Lister tous les utilisateurs
    public function index()
    {
        $utilisateurs = Utilisateur::with(['commandes','paniers'])->get();
        return response()->json($utilisateurs,200);
    }

    // Créer un utilisateur
    public function store(Request $request)
    {
        $request->validate([
            'nomUtilisateur'=>'required|string|max:100',
            'email'=>'required|email|unique:utilisateurs,email',
            'contact'=>'required|string|max:10',
            'motDePasse'=>'required|string|min:6',
            'role'=>'required|in:admin,client',
        ]);

        $utilisateur = Utilisateur::create([
            'nomUtilisateur'=>$request->nomUtilisateur,
            'email'=>$request->email,
            'contact'=>$request->contact,
            'motDePasse'=>Hash::make($request->motDePasse),
            'role'=>$request->role
        ]);

        return response()->json($utilisateur->load(['commandes','paniers']),201);
    }

    // Afficher un utilisateur
    public function show(string $id)
    {
        $utilisateur = Utilisateur::with(['commandes','paniers'])->findOrFail($id);
        return response()->json($utilisateur,200);
    }

    // Modifier un utilisateur
    public function update(Request $request, string $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $request->validate([
            'nomUtilisateur'=>'sometimes|string|max:100',
            'email'=>'sometimes|email|unique:utilisateurs,email',
            'contact'=>'sometimes|string|max:10',
            'motDePasse'=>'sometimes|string|min:6',
            'role'=>'sometimes|in:admin,client',
        ]);

        if ($request->filled('motDePasse')) {
            $request->merge(['motDePasse'=>Hash::make($request->motDePasse)]);
        }

        $utilisateur->update($request->all());
        return response()->json($utilisateur->load(['commandes','paniers']),200);
    }

    // Supprimer un utilisateur
    public function destroy(string $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $utilisateur->delete();
        return response()->json(['message'=>'Utilisateur supprimé'],200);
    }
}
