<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProduitController extends Controller
{
    public function index()
    {
             return response()->json(Produit::with('categorie', 'promotion')->get(), 200);
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'nomProduit' => 'required|string|max:255',
        'prix' => 'required|numeric|min:0',
        'poids' => 'required|numeric|min:0',
        'quantiteStock' => 'required|integer|min:0',
        'numCategorie' => 'required|exists:categories,numCategorie',
        'image' => 'required|image|mimes:jpg,jpeg,png|max:153600',
        'numPromotion' => 'nullable|exists:promotions,numPromotion',
    ]);

    $produit = new Produit();
    $produit->nomProduit = $validated['nomProduit'];
    $produit->prix = $validated['prix'];
    $produit->poids = $validated['poids'];
    $produit->quantiteStock = $validated['quantiteStock'];
    $produit->numCategorie = $validated['numCategorie'];

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images/produits', 'public');
        $produit->image = '/storage/' . $imagePath;
    }

    if (!empty($validated['numPromotion'])) {
        $produit->numPromotion = $validated['numPromotion'];
    }

    $produit->save();

    return response()->json($produit, 201);
}

    public function show(string $id)
    {
        $produit = Produit::with('categorie', 'promotion')->findOrFail($id);
        return response()->json($produit, 200);
    }

   public function update(Request $request, $id)
{
    $produit = Produit::findOrFail($id);

    $validated = $request->validate([
        'nomProduit' => 'required|string|max:255',
        'prix' => 'required|numeric|min:0',
        'poids' => 'required|numeric|min:0',
        'quantiteStock' => 'required|integer|min:0',
        'numCategorie' => 'required|exists:categories,numCategorie',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:153600',
        'numPromotion' => 'nullable|exists:promotions,numPromotion',
    ]);

    $produit->nomProduit = $validated['nomProduit'];
    $produit->prix = $validated['prix'];
    $produit->poids = $validated['poids'];
    $produit->quantiteStock = $validated['quantiteStock'];
    $produit->numCategorie = $validated['numCategorie'];

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('images/produits', 'public');
        $produit->image = '/storage/' . $imagePath;
    }

    $produit->numPromotion = $validated['numPromotion'] ?? null;

    $produit->save();

    return response()->json($produit, 200);
}

    public function destroy(string $id)
    {
        $produit = Produit::findOrFail($id);
        
        if ($produit->image) {
            $path = str_replace(Storage::url(''), '', $produit->image);
            Storage::disk('public')->delete($path);
        }
        
        $produit->delete();
        return response()->json(null, 204);
    }
}