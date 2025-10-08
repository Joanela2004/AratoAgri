<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produit;
use Illuminate\Support\Facades\Storage;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
        $produit =Produit::with(['promotion','categorie','detailCommande.commande','detailPanier.panier'])->get();
        return response()->json($produit,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $request->validate([
                'nomProduit'=>'required|string|max:100',
                'prix'=>'required|numeric|min:0',
                'poids'=>'required|numeric|min:0',
                'quantiteStock'=>'required|integer|min:0',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
                'numCategorie'=>'required|exists:categories,numCategorie',
                'numPromotion' => 'nullable|exists:promotions,numPromotion'
        ]); 
        
        //televerser l image
       $cheminImage = $request->file('image')->store('produits', 'public');

        
        $produit = Produit::create([
            'nomProduit'=>$request->nomProduit,
            'prix'=>$request->prix,
            'poids'=>$request->poids,
            'quantiteStock'=>$request->quantiteStock,
            'image'=>$cheminImage,
            'numCategorie'=>$request->numCategorie,
            'numPromotion' => $request->numPromotion,
        ]);

      
        return response()->json($produit->load(['promotion','categorie','detailCommande.commande','detailPanier.panier']),201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $produit=Produit::with(['promotion','categorie','detailCommande.commande','detailPanier.panier'])->findOrFail($id);
        return response()->json($produit,200);
    }

    /**
     * Update the specified resource in storage.
     */
    //modifier un produit
        public function update(Request $request, string $id)
    {
    $produit = Produit::findOrFail($id);

    $validated = $request->validate([
        'nomProduit' => 'sometimes|string|max:100',
        'prix' => 'sometimes|numeric|min:0',
        'poids' => 'sometimes|numeric|min:0',
        'quantiteStock' => 'sometimes|integer|min:0',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        'numCategorie' => 'sometimes|exists:categories,numCategorie',
        'numPromotion' => 'nullable|exists:promotions,numPromotion',
    ]);

    // Gestion de l'image
    if ($request->hasFile('image')) {
        // Supprimer l'ancienne image si elle existe physiquement
        if (!empty($produit->image) && Storage::disk('public')->exists($produit->image)) {
            Storage::disk('public')->delete($produit->image);
        }

        // Enregistrer la nouvelle
        $cheminImage = $request->file('image')->store('produits', 'public');
        $validated['image'] = $cheminImage;
    }

 
    $produit->update($validated);

    
    return response()->json(
        $produit->load(['promotion','categorie','detailCommande.commande','detailPanier.panier']),
        200
    );
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $produit=Produit::findOrFail($id);

        //supprimer image
        if($produit->image){
           Storage::disk('public')->delete($produit->image);

        }

        $produit->delete();
        return response()->json([
            'message'=>'Produit supprimé avec succès'
        ],200);
    }
}
