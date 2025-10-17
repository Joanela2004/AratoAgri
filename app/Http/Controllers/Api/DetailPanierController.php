<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetailPanier;
use App\Models\Panier;

class DetailPanierController extends Controller
{
    public function index()
    {
        $details = DetailPanier::with(['produit','panier.utilisateur'])
            ->whereHas('panier', fn($q) => $q->where('numUtilisateur', auth()->id()))
            ->get();
        return response()->json($details, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'numPanier'=>'required|exists:paniers,numPanier',
            'numProduit'=>'required|exists:produits,numProduit',
            'poids'=>'required|numeric|min:1',
            'decoupe'=>'required|string|max:100',
        ]);

        $panier = Panier::where('numPanier', $request->numPanier)
                        ->where('numUtilisateur', auth()->id())
                        ->firstOrFail();

        $detail = DetailPanier::create([
            'numPanier' => $panier->numPanier,
            'numProduit' => $request->numProduit,
            'poids' => $request->poids,
            'decoupe' => $request->decoupe
        ]);

        return response()->json([
            'success' => true,
            'data' => $detail->load(['produit', 'panier.utilisateur'])
        ], 201);
    }

    public function show(string $id)
    {
        $detail = DetailPanier::with(['produit','panier.utilisateur'])
            ->whereHas('panier', fn($q) => $q->where('numUtilisateur', auth()->id()))
            ->findOrFail($id);
        return response()->json($detail, 200);
    }

    public function update(Request $request, string $id)
    {
        $detail = DetailPanier::whereHas('panier', fn($q) => $q->where('numUtilisateur', auth()->id()))
            ->findOrFail($id);

        $request->validate([
            'poids'=>'sometimes|numeric|min:0.01',
            'decoupe'=>'sometimes|string|max:100',
        ]);

        $detail->update($request->only(['poids','decoupe']));

        return response()->json($detail->load(['produit','panier.utilisateur']), 200);
    }

    public function destroy(string $id)
    {
        $detail = DetailPanier::whereHas('panier', fn($q) => $q->where('numUtilisateur', auth()->id()))
            ->findOrFail($id);
        $detail->delete();
        return response()->json(['message'=>'Produit retiré du panier'],200);
    }
}
