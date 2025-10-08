<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ModePaiement;
class ModePaiementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //lister tous les details de panier
    public function index()
    {
        return response()->json(ModePaiement::all(),200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['nomModePaiement'=>'required|string|max:100']);
        $modePaiement = ModePaiement::create($request->all());
        return response()->json($modePaiement,201);
    }

    /**
     * Display the specified resource.
     */

    //afficher un mode paiement
    public function show(string $id)
    {
       $modePaiement=ModePaiement::findOrFail($id);
       return response()->json($modePaiement,200);
    }

    /**
     * Update the specified resource in storage.
     */

    //modifer un mode de Paiement
    public function update(Request $request, string $id)
    {
        $modePaiement= ModePaiement::findOrFail($id);
        $request->validate([
            'nomModePaiement'=>'sometimes|string|max:100'
        ]);
        $modePaiement->update($request->all());
        return response()->json($modePaiement,200);
    }

    /**
     * Remove the specified resource from storage.
     */
     //supprimer un mode de paiement 
    public function destroy(string $id)
    {
       $modePaiement=ModePaiement::findOrFail($id);
       $modePaiement->delete(); 
       return response()->json(['message'=>'Mode de paiement supprimé']);
    }
}
