<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Panier;
use App\Models\DetailPanier;
use App\Models\Produit;
use Illuminate\Support\Facades\DB;

class PanierController extends Controller
{
    public function index()
{
    try {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        $panier = Panier::with(['detailsPaniers.produit.categorie'])
            ->where('numUtilisateur', $userId)
            ->where('statut', 'en_cours')
            ->first();

        if (!$panier) {
            return response()->json([]);
        }

        $formattedDetails = $panier->detailsPaniers->map(function ($detail) {
            return [
                'numDetailPanier' => $detail->numDetailPanier,
                'numProduit' => $detail->numProduit,
                'poids' => $detail->poids,
                'prixUnitaire' => $detail->prixUnitaire,
                'sousTotal' => $detail->sousTotal,
                'decoupe' => $detail->decoupe,
                'produit' => $detail->produit ? [
                    'numProduit' => $detail->produit->numProduit,
                    'nomProduit' => $detail->produit->nomProduit,
                    'prix' => $detail->produit->prix,
                    'image' => $detail->produit->image,
                    'poids' => $detail->produit->poids,
                    'categorie' => $detail->produit->categorie ? [
                        'numCategorie' => $detail->produit->categorie->numCategorie,
                        'nomCategorie' => $detail->produit->categorie->nomCategorie
                    ] : null
                ] : null
            ];
        });

        return response()->json($formattedDetails);

    } catch (\Exception $e) {
        \Log::error('Erreur lors de la récupération du panier : ' . $e->getMessage());
        return response()->json(['message' => 'Erreur interne du serveur.'], 500);
    }
}

    // Ajouter un produit au panier
    public function store(Request $request)
    {
        $request->validate([
            'numProduit' => 'required|exists:produits,numProduit',
            'poids' => 'required|numeric|min:0.01'
        ]);

        $userId = auth()->id();

        // Récupérer ou créer le panier "en cours"
        $panier = Panier::firstOrCreate(
            ['numUtilisateur' => $userId, 'statut' => 'en_cours']
        );

        $produit = Produit::find($request->numProduit);

        // Vérifier la quantité disponible
        if ($request->poids > $produit->poids) {
            return response()->json([
                'message' => "Stock insuffisant pour {$produit->nomProduit}. Disponible : {$produit->poids} kg"
            ], 422);
        }

        // Vérifier si le produit est déjà dans le panier
        $detail = DetailPanier::firstOrNew([
            'numPanier' => $panier->numPanier,
            'numProduit' => $request->numProduit,
        ]);

        $detail->poids = $request->poids;
        $detail->prixUnitaire = $produit->prix;
        $detail->sousTotal = $produit->prix * $request->poids;
        $detail->save();

        return response()->json($panier->load('detailsPaniers.produit'), 201);
    }

    // Mettre à jour un produit du panier
    public function update(Request $request, $id)
    {
        $detail = DetailPanier::findOrFail($id);
        $request->validate([
            'poids' => 'required|numeric|min:0.01'
        ]);

        $produit = $detail->produit;

        if ($request->poids > $produit->poids) {
            return response()->json([
                'message' => "Stock insuffisant pour {$produit->nomProduit}. Disponible : {$produit->poids} kg"
            ], 422);
        }

        $detail->poids = $request->poids;
        $detail->sousTotal = $produit->prix * $request->quantite;
        $detail->save();

        return response()->json($detail->load('produit'), 200);
    }

    // Supprimer un produit du panier
    public function destroy($id)
    {
        $detail = DetailPanier::findOrFail($id);
        $detail->delete();

        return response()->json(['message' => 'Produit retiré du panier'], 200);
    }

    // Vider le panier
    public function clear()
    {
        $userId = auth()->id();
        $panier = Panier::where('numUtilisateur', $userId)->where('statut', 'en_cours')->first();

        if ($panier) {
            $panier->detailsPanier()->delete();
        }

        return response()->json(['message' => 'Panier vidé'], 200);
    }
    
}
