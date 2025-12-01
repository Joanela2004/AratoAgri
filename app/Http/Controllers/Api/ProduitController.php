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
        $produits = Produit::with(['categorie', 'promotion'])->get();
        return response()->json($produits);
    }

    public function show($id)
    {
        $produit = Produit::with(['categorie', 'promotion'])->find($id);
        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }
        return response()->json($produit);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomProduit' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0',
            'poids' => 'required|numeric|min:0',
            
            'image' => 'nullable|image|max:2048',
            'numCategorie' => 'required|exists:categories,numCategorie',
            'numPromotion' => 'nullable|exists:promotions,numPromotion'
        ]);

        $data = $validated;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('produits', 'public');
            $data['image'] = $path;
        }

        $produit = Produit::create($data);

        return response()->json($produit, 201);
    }

    public function update(Request $request, $id)
    {
        $produit = Produit::find($id);
        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $validated = $request->validate([
            'nomProduit' => 'sometimes|required|string|max:255',
            'prix' => 'sometimes|required|numeric|min:0',
            'poids' => 'sometimes|required|numeric|min:0',
           
            'image' => 'nullable|image|max:2048',
            'numCategorie' => 'sometimes|required|exists:categories,numCategorie',
            'numPromotion' => 'nullable|exists:promotions,numPromotion'
        ]);

        $data = $validated;

        if ($request->hasFile('image')) {
            if ($produit->image) {
                Storage::disk('public')->delete($produit->image);
            }
            $path = $request->file('image')->store('produits', 'public');
            $data['image'] = $path;
        }

        $produit->update($data);

        return response()->json($produit);
    }

    public function destroy($id)
    {
        $produit = Produit::find($id);
        if (!$produit) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        if ($produit->image) {
            Storage::disk('public')->delete($produit->image);
        }

        $produit->delete();

        return response()->json(['message' => 'Produit supprimé']);
    }
}