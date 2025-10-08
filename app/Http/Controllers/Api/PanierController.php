<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Panier;

class PanierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
     $panier = Panier::with(['utilisateur','detailPaniers.produit'])->get();
     return response()->json($panier,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    //creer un panier
    public function store(Request $request)
    {
        $request->validate([
            'numUtilisateur'=>'required|exists:utilisateurs,numUtilisateur',
            'dateCreation'=>'nullable|date'
        ]);
        $panier = Panier::create([
            'numUtilisateur'=>$request->numUtilisateur,
            'dateCreation'=>now()
        ]);
        
        return response()->json($panier->load(['utilisateur','detailPaniers.produit']),201);
    }

    /**
     * Display the specified resource.
     */
    //afficher un panier
    public function show(string $id)
    {
        $panier = Panier::with(['utilisateur','detailPaniers.produit'])->findOrFail($id);
        return response()->json($panier, 200);
        
    }

    /**
     * Update the specified resource in storage.
     */
    //modifier un panier
    public function update(Request $request, string $id)
    {
        $panier = Panier::findOrFail($id);
        $request->validate([
            'numUtilisateur'=>'sometimes|exists:utilisateurs,numUtilisateur'
        ]);
        $panier->update($request->only('numUtilisateur'));
        return response()->json($panier->load('utilisateur','detailPaniers.produit'),200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $panier = Panier::findOrFail($id);
        $panier->delete();
        return response()->json(['message' => 'Panier supprimé'], 200);
    }
}
