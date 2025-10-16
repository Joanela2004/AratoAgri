<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paiement;
class PaiementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //lister tous les paiements
    public function index()
    {
       $paiements = Paiement::with([
        'commande','modePaiement'
       ])->get();
       return response()->json($paiements,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    //creer un paiement
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'numCommande'=>'required|exists:commandes,numCommande',
    //         'numModePaiement'=>'required|exists:mode_paiements,numModePaiement',
    //         'statut'=>'required|string|max:50',
    //         'datePaiement'=>'required|date'
    //     ]);
    //     $paiement = Paiement::create($request->all());
    //     return response()->json($paiement->load(['commande','modePaiement'],201));
    // }

    /**
     * Display the specified resource.
     */
    //afficher un paiement
    public function show(string $id)
    {
        $paiement=Paiement::with(['commande','modePaiement'])->findOrFail($id);
        return response()->json($paiement,200);
    }

    /**
     * Update the specified resource in storage.
     */
    //modifer un paiement
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
        return response()->json($paiement->load([
            'commande','modePaiement'
        ]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    $paiement = Paiement::findOrFail($id);
    $paiement->delete();
    return response()->json(['message'=>'Paiement suprrimé'],200);
    }
}
