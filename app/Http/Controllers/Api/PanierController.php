<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Panier;

class PanierController extends Controller
{
    public function index()
    {
        $paniers = Panier::with(['utilisateur','detailPaniers.produit'])
                        ->where('numUtilisateur', auth()->id())
                        ->get();
        return response()->json($paniers,200);
    }

    public function store(Request $request)
    {
        $panier = Panier::create([
            'numUtilisateur' => auth()->id(),
            'dateCreation' => now()
        ]);
        return response()->json($panier->load(['utilisateur','detailPaniers.produit']),201);
    }

    public function show(string $id)
    {
        $panier = Panier::with(['utilisateur','detailPaniers.produit'])
                        ->where('numUtilisateur', auth()->id())
                        ->findOrFail($id);
        return response()->json($panier,200);
    }

    public function update(Request $request, string $id)
    {
        $panier = Panier::where('numUtilisateur', auth()->id())->findOrFail($id);
        $panier->update($request->only('dateCreation'));
        return response()->json($panier->load(['utilisateur','detailPaniers.produit']),200);
    }

    public function destroy(string $id)
    {
        $panier = Panier::where('numUtilisateur', auth()->id())->findOrFail($id);
        $panier->delete();
        return response()->json(['message' => 'Panier supprimé'], 200);
    }
}
