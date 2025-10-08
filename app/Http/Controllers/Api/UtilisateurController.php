<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Utilisateur;
class UtilisateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $utilisateur=Utilisateur::with(['commandes','paniers'])->get();
        return response()->json($utilisateur,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    //creer un utilisateur
    public function store(Request $request)
    {
        $request->validate([
            'nomUtilisateur'=>'required|string|max:100',
            'email'=>'required|email|unique:utilisateurs,email',
            'contact'=>'required|string|max:10',
            'motDePasse'=>'required|string|min:6|max:10',
            'role'=>'required|in:admin,client',
        ]);
        $utilisateur = Utilisateur::create($request->all());
        return response()->json($utilisateur->load(['commandes','paniers']),201);
    }           
    

    /**
     * Display the specified resource.
     */
    //afficher un utilisateur
    public function show(string $id)
    {
        $utilisateur=Utilisateur::with(['commandes','paniers'])->findOrFail($id);
        return response()->json($utilisateur,200);
    }

    /**
     * Update the specified resource in storage.
     */
    //modifier un utilisateur
    public function update(Request $request, string $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $request->validate([
            'nomUtilisateur'=>'sometimes|string|max:100',
            'email'=>'sometimes|email|unique:utilisateurs,email',
            'contact'=>'sometimes|string|max:10',
            'motDePasse'=>'sometimes|string|min:6|max:10',
            'role'=>'sometimes|in:admin,client',
        ]);
        $utilisateur->update($request->all());
        return response()->json($utilisateur->load(['commandes','paniers']),200);
    }

    /**
     * Remove the specified resource from storage.
     */
    //supprimer un utilisateur
    public function destroy(string $id)
    {
        $utilisateur=Utilisateur::findOrFail($id);
        $utilisateur->delete();
        return response()->json($utilisateur,200);
    }
}
