<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livreur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // ← AJOUTER CET IMPORT pour crypter le mot de passe

class LivreurController extends Controller
{
    /**
     * Display a listing of the resource.
     */

 
    public function index()
    {
        $livreurs = Livreur::all();

        return response()->json([
            'success' => true,
            'data' => $livreurs
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|unique:livreurs',
            'email' => 'required|email|unique:livreurs', // Mis à 'required' car obligatoire pour le login
            'password' => 'required|string|min:6',        // ← AJOUTER LA VALIDATION DU PASSWORD
            'statut' => 'nullable|in:disponible,en_livraison,indisponible',
        ]);

        $livreur = Livreur::create([
            'nom' => $request->nom,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'password' => Hash::make($request->password), // ← HACHER LE MOT DE PASSE ICI
            'statut' => $request->statut ?? 'disponible',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Livreur créé avec succès',
            'data' => $livreur
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $livreur = Livreur::find($id);

        if (!$livreur) {
            return response()->json([
                'success' => false,
                'message' => 'Livreur introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $livreur
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $livreur = Livreur::find($id);

        if (!$livreur) {
            return response()->json([
                'success' => false,
                'message' => 'Livreur introuvable'
            ], 404);
        }

        $request->validate([
            'nom' => 'sometimes|string|max:255',
            'telephone' => 'sometimes|string|unique:livreurs,telephone,' . $id,
            'email' => 'sometimes|email|unique:livreurs,email,' . $id,
            'password' => 'sometimes|nullable|string|min:6', // ← PERMETTRE LA MODIFICATION OPTIONNELLE
            'statut' => 'nullable|in:disponible,en_livraison,indisponible',
        ]);

        // On récupère toutes les données de la requête
        $data = $request->all();

        // Si un nouveau mot de passe est fourni, on le hache. Sinon, on retire la clé pour ne pas écraser l'ancien
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $livreur->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Livreur mis à jour avec succès',
            'data' => $livreur
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $livreur = Livreur::find($id);

        if (!$livreur) {
            return response()->json([
                'success' => false,
                'message' => 'Livreur introuvable'
            ], 404);
        }

        $livreur->delete();

        return response()->json([
            'success' => true,
            'message' => 'Livreur supprimé avec succès'
        ]);
    }
}