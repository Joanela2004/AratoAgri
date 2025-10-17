<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paiement;

class PaiementController extends Controller
{
    // Lister tous les paiements
    public function index()
    {
        $paiements = Paiement::with(['commande','modePaiement'])->get();
        return response()->json($paiements,200);
    }

    // Afficher un paiement
    public function show(string $id)
    {
        $paiement = Paiement::with(['commande','modePaiement'])->findOrFail($id);
        return response()->json($paiement,200);
    }

    // Modifier un paiement
    public function update(Request $request, string $id)
    {
        $paiement = Paiement::findOrFail($id);
        $request->validate([
            'numCommande'=>'sometimes|exists:commandes,numCommande',
            'numModePaiement'=>'sometimes|exists:mode_paiements,numModePaiement',
            'statut'=>'sometimes|string|max:50',
            'datePaiement'=>'sometimes|date'
        ]);
        $paiement->update($request->all());
        return response()->json($paiement->load(['commande','modePaiement']),200);
    }

    // Supprimer un paiement
    public function destroy(string $id)
    {
        $paiement = Paiement::findOrFail($id);
        $paiement->delete();
        return response()->json(['message'=>'Paiement supprimé'],200);
    }
}
