<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetailPanier;
class DetailPanierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
          $detail = DetailPanier::with(['produit','panier.utilisateur'])->get();
        return response()->json($detail,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
       $request->validate([
        'numPanier'=>'required|exists:paniers,numPanier',
        'numProduit'=>'required|exists:produits,numProduit',
        'poids'=>'required|numeric|min:1',
        'decoupe'=>'required|string|max:100',

       ]);
       $detail = DetailPanier::create($request->only(['numPanier', 'numProduit', 'poids', 'decoupe']));

       return response()->json([
            'success' => true,
            'data'    => $detail->load(['produit', 'panier.utilisateur'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
     public function show(string $id)
    {
        $detail = DetailPanier::with(['produit','panier.utilisateur'])->findOrFail($id);
        return response()->json($detail,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $detail = DetailPanier::findOrFail($id);
       $request->validate([
        'poids'=>'sometimes|numeric|min:0.01',
        'decoupe'=>'sometimes|string|max:100',
    ]);

    $detail->update($request->only(['poids','decoupe']));
    return response()->json($detail->load(['produit','panier.utilisateur']), 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
          $detail=DetailPanier::findOrFail($id);
        $detail->delete();
        return response()->json(['message'=>'Produit retiré du panier'],200);
    }
}



   

    /**
     * Store a newly created resource in storage.
     */
    //ajouter un produit au panier
   

    /**
     * Display the specified resource.
     */
    //afficher un detailPanier
   

    /**
     * Update the specified resource in storage.
     */

    //modifier un produit dans le panier
    

 

