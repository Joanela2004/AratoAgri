<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ModePaiement;

class ModePaiementController extends Controller
{
    // Lister tous les modes de paiement
    public function index()
    {
        return response()->json(ModePaiement::all(),200);
    }

    // Créer un mode de paiement
    public function store(Request $request)
    {
        $request->validate(['nomModePaiement'=>'required|string|max:100']);
        $modePaiement = ModePaiement::create($request->all());
        return response()->json($modePaiement,201);
    }

    // Afficher un mode de paiement
    public function show(string $id)
    {
        $modePaiement = ModePaiement::findOrFail($id);
        return response()->json($modePaiement,200);
    }

    // Modifier un mode de paiement
    public function update(Request $request, string $id)
    {
        $modePaiement = ModePaiement::findOrFail($id);
        $request->validate(['nomModePaiement'=>'sometimes|string|max:100']);
        $modePaiement->update($request->all());
        return response()->json($modePaiement,200);
    }

    // Supprimer un mode de paiement
    public function destroy(string $id)
    {
        $modePaiement = ModePaiement::findOrFail($id);
        $modePaiement->delete();
        return response()->json(['message'=>'Mode de paiement supprimé'],200);
    }
}
