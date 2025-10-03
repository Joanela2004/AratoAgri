<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produit;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Produit::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $DonneeValide = $request->validate([
                'nomProduit'=>'required|string|max:100',
                'prix'=>'required|numeric',
                'poids'=>'required|numeric',
                'quantiteStock'=>'required|numeric',
                'image'=>'required|string|max:2048'
        ]);
        
        
        $produit = Produit::create($DonneeValide);
        return response()->json($produit,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(Produit::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
       $produit = Produit::findOrFail($id);
       $DonneeValide=$request->validate([
        'nomProduit'=>'sometimes|required|string|max:255',
        'prix'=>'sometimes|required|numeric',
        'quantiteStock'=>'sometimes|required|integer',
        'image'=>'nullable|image|max:1040',
       ]);
  
       $produit->update($DonneeValide);
       return response()->json($produit);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $produit=Produit::findOrFail($id);
        $produit->delete();
        return response()->json([
            'message'=>'Produit supprimé avec succès'
        ]);
    }
}
