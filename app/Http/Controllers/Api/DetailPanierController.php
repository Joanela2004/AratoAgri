<?php

namespace App\Http\Controllers\Api;

use App\Models\DetailPanier;
use Illuminate\Http\Request;

class DetailPanierController extends Controller
{
    // Ajouter un produit au panier
    public function store(Request $request)
    {
        $request->validate([
            'numPanier' => 'required|exists:paniers,numPanier',
            'numProduit' => 'required|exists:produits,numProduit',
            'poids' => 'required|numeric|min:1',
            'prixUnitaire' => 'required|numeric|min:0',
        ]);

        $sousTotal = $request->poids * $request->prixUnitaire;

        $detail = DetailPanier::create([
            'numPanier' => $request->numPanier,
            'numProduit' => $request->numProduit,
            'poids' => $request->poids,
            'prixUnitaire' => $request->prixUnitaire,
            'sousTotal' => $sousTotal,
            'numDecoupe' => $request->numDecoupe ?? null,
        ]);

        return response()->json($detail, 201);
    }

    // Modifier un produit dans le panier
    public function update(Request $request, $id)
    {
        $detail = DetailPanier::find($id);
        if (!$detail) {
            return response()->json(['message' => 'Produit non trouvé dans le panier'], 404);
        }

        $detail->poids = $request->poids ?? $detail->poids;
        $detail->prixUnitaire = $request->prixUnitaire ?? $detail->prixUnitaire;
        $detail->sousTotal = $detail->poids * $detail->prixUnitaire;
        $detail->save();

        return response()->json($detail);
    }

    // Supprimer un produit du panier
    public function destroy($id)
    {
        $detail = DetailPanier::find($id);
        if (!$detail) {
            return response()->json(['message' => 'Produit non trouvé dans le panier'], 404);
        }

        $detail->delete();
        return response()->json(['message' => 'Produit supprimé du panier']);
    }
}
