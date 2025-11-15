<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaiementController extends Controller
{
    public function index()
    {
        return response()->json(Paiement::with('commande','modePaiement')->get());
    }

    public function show($id)
    {
        $paiement = Paiement::with('commande','modePaiement')->find($id);
        if(!$paiement) return response()->json(['message' => 'Paiement non trouvé'], 404);
        return response()->json($paiement);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numCommande' => 'required|exists:commandes,numCommande',
            'numModePaiement' => 'required|exists:mode_paiements,numModePaiement',
            'montantApayer' => 'required|numeric',
            'statut' => 'required|in:en attente,effectué,echoué',
            'datePaiement' => 'nullable|date',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $paiement = Paiement::create($request->all());
        return response()->json($paiement, 201);
    }

    public function update(Request $request, $id)
    {
        $paiement = Paiement::find($id);
        if(!$paiement) return response()->json(['message' => 'Paiement non trouvé'], 404);

        $paiement->update($request->all());
        return response()->json($paiement);
    }

    public function destroy($id)
    {
        $paiement = Paiement::find($id);
        if(!$paiement) return response()->json(['message' => 'Paiement non trouvé'], 404);

        $paiement->delete();
        return response()->json(['message' => 'Paiement supprimé']);
    }
}
