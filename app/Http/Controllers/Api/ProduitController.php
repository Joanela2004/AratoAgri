<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produit;
use Illuminate\Support\Facades\Storage;

class ProduitController extends Controller
{
    public function index()
    {
        $produits = Produit::with(['promotion', 'categorie'])->get();
        return response()->json($produits, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomProduit' => 'required|string|max:100',
            'prix' => 'required|numeric|min:0',
            'poids' => 'required|numeric|min:0',
            'quantiteStock' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'numCategorie' => 'required|exists:categories,numCategorie',
            'numPromotion' => 'nullable|exists:promotions,numPromotion'
        ]);

        $cheminImage = $request->file('image')->store('produits', 'public');

        $produit = Produit::create([
            'nomProduit' => $request->nomProduit,
            'prix' => $request->prix,
            'poids' => $request->poids,
            'quantiteStock' => $request->quantiteStock,
            'image' => $cheminImage,
            'numCategorie' => $request->numCategorie,
            'numPromotion' => $request->numPromotion
        ]);

        return response()->json($produit->load(['promotion', 'categorie']), 201);
    }

    public function show(string $id)
    {
        $produit = Produit::with(['promotion', 'categorie'])->findOrFail($id);
        return response()->json($produit, 200);
    }

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
            'numPromotion' => 'nullable|exists:promotions,numPromotion'
        ]);

        if ($request->hasFile('image')) {
            if ($produit->image && Storage::disk('public')->exists($produit->image)) {
                Storage::disk('public')->delete($produit->image);
            }
            $validated['image'] = $request->file('image')->store('produits', 'public');
        }

        $produit->update($validated);

        return response()->json($produit->load(['promotion', 'categorie']), 200);
    }

    public function destroy(string $id)
    {
        $produit = Produit::findOrFail($id);

        if ($produit->image && Storage::disk('public')->exists($produit->image)) {
            Storage::disk('public')->delete($produit->image);
        }

        $produit->delete();
        return response()->json(['message' => 'Produit supprimé avec succès'], 200);
    }
}
