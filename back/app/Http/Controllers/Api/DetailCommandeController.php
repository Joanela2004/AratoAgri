<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetailCommande;
class DetailCommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    //lister tous les details de commande
    public function index()
    {
        $details = DetailCommande::with(['commande','produit.categorie' ])->get();
        return response()->json($details,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    //ajouter un produit a une commande
    public function store(Request $request)
    {
        $request->validate([
            'numCommande'=>'required|exists:commandes,numCommande',
            'numProduit'=>'required|exists:produits,numProduit',
            'poids'=>'required|integer|min:1',
            'prixUnitaire'=>'required|numeric|min:0'
        ]);
        $detail=DetailCommande::create($request->all());
        return response()->json($detail->load(['commande','produit'],201));
    }

    /**
     * Display the specified resource.
     */
    //afficher un detail commande
    public function show(string $id)
    {
        $detail = DetailCommande::with([
            'commande','produit'
        ])->findOrFail($id);
        return response()->json($detail,200);
    }

    /**
     * Update the specified resource in storage.
     */
    //modifier un detail commande
    public function update(Request $request, string $id)
    {
        $detail = DetailCommande::findOrFail($id);
        $request->validate([
            'numCommande'=>'sometimes|exists:commandes,numCommande',
            'numProduit'=>'sometimes|exists:produits,numProduit',
            'poids'=>'sometimes|integer|min:1',
            'prixUnitaire'=>'sometimes|numeric|min:0'
        ]);
        $detail->update($request->all());
        return response()->json($detail->load(['commande','produit']));
    }

    /**
     * Remove the specified resource from storage.
     */
    //supprimer un detail de commande
    public function destroy(string $id)
    {
    $detail = DetailCommande::findOrFail($id);
    $detail->delete();
    return response()->json(['message'=>'Produit retiré de la commande'],200);
    }
}
