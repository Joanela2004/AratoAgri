<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FraisLivraison;
class FraisLivraisonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    return response()->json(FraisLivraison::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
$validated = $request->validate([
            'poidsMin' => 'required|numeric|min:0',
            'poidsMax' => 'required|numeric|gt:poidsMin',
            'frais' => 'required|numeric|min:0',
        ]);

        $frais = FraisLivraison::create($validated);
        return response()->json(['message' => 'Frais de livraison ajouté', 'data' => $frais], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $frais = FraisLivraison::find($numFrais);
        if(!$frais){
            return response()->json(['message'=>'frais non trouvé'],400);
        }
        return response()->json($frais);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $frais = FraisLivraison::find($numFrais);
        if (!$frais) {
            return response()->json(['message' => 'Frais non trouvé'], 404);
        }
        $validated=$request->validate([
            'poidsMin' => 'sometimes|numeric|min:0',
            'poidsMax' => 'sometimes|numeric|gt:poidsMin',
            'frais' => 'sometimes|numeric|min:0',
        ]);

        $frais->update($validated);
        return response()->json([
            'message'=>'Frais de livraison mis a jour avec succes'
        ],200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $frais = FraisLivraison::find($numFrais);

        if (!$frais) {
            return response()->json(['message' => 'Frais non trouvé'], 404);
        }

        $frais->delete();

        return response()->json(['message' => 'Frais supprimé avec succès']);

    }
}
