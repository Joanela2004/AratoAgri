<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProduitController extends Controller
{
    public function index()
{
    return Produit::with(['categorie','promotion'])
        ->orderBy("numProduit", "DESC")
        ->get();
}


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomProduit' => 'required|string|unique:produits,nomProduit',
            'prix' => 'required|numeric',
            'poids' => 'required|numeric',
            'numCategorie' => 'required|exists:categories,numCategorie',
            'numPromotion' => 'nullable|exists:promotions,numPromotion',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {

            // Vérifier si le produit existe en soft delete
            $deleted = Produit::onlyTrashed()
                ->where("nomProduit", $request->nomProduit)
                ->first();

            if ($deleted) {
                return response()->json([
                    "soft_deleted" => true,
                    "produit_id" => $deleted->numProduit,
                    "produit_nom" => $deleted->nomProduit
                ], 409);
            }

            return response()->json(["message" => $validator->errors()->first()], 422);
        }

        $produit = new Produit($request->except("image"));

        if ($request->hasFile("image")) {
            $produit->image = $request->file("image")->store("produits", "public");
        }

        $produit->save();

        return response()->json($produit, 201);
    }

    public function update(Request $request, $id)
    {
        $produit = Produit::findOrFail($id);

        $produit->fill($request->except("image"));

        if ($request->hasFile("image")) {
            $produit->image = $request->file("image")->store("produits", "public");
        }

        $produit->save();
        return response()->json($produit);
    }

   public function destroy($id)
{
    $produit = Produit::withTrashed()->find($id);
    if (!$produit) {
        return response()->json(["message" => "Produit non trouvé"], 404);
    }
    $produit->delete();
    return response()->json(["message" => "Produit supprimé"]);
}


    public function restore($id)
    {
        Produit::onlyTrashed()->where("numProduit", $id)->restore();
        return response()->json(["message" => "Produit restauré"]);
    }
}
