<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Livraison;
use App\Models\Commande;

class LivraisonController extends Controller
{

    public function index()
    {
        $livraisons = Livraison::with(['commande.utilisateur'])->get();
        return response()->json($livraisons, 200);
    }

    public function indexClient()
    {
        $livraisons = Livraison::with(['commande'])
            ->whereHas('commande', function($q){
                $q->where('numUtilisateur', auth()->id());
            })
            ->get();
        return response()->json($livraisons, 200);
    }

  public function showByCommandeClient($numCommande)
    {
         $livraisons = Livraison::with(['commande'])->where('numCommande', $numCommande)->get();
    return response()->json($livraisons, 200);

    }
public function update(Request $request, $id)
{
    $livraison = Livraison::findOrFail($id);

    $request->validate([
        'statutLivraison' => 'required|string',
        'transporteur' => 'sometimes|string',
        'contactTransporteur' => 'sometimes|nullable|string',
    ]);

    DB::beginTransaction();
    try {
        $livraison->statutLivraison = $request->statutLivraison;

        if ($request->has('transporteur')) {
            $livraison->transporteur = $request->transporteur;
        }

        if ($request->has('contactTransporteur')) {
            $livraison->contactTransporteur = $request->contactTransporteur;
        }

        if ($request->statutLivraison === 'en cours' && !$livraison->dateExpedition) {
            $livraison->dateExpedition = now();
        }

        if ($request->statutLivraison === 'livrée') {
            $livraison->dateLivraison = now();
        }

        $livraison->save();
        DB::commit();

        return response()->json($livraison->load(['commande']), 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Erreur lors de la mise à jour de la livraison',
            'msg' => $e->getMessage()
        ], 500);
    }
}

    public function destroy($id)
    {
        $livraison = Livraison::findOrFail($id);
        DB::beginTransaction();
        try {
            $livraison->delete();
            DB::commit();
            return response()->json(['message'=>'Livraison supprimée'],200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error'=>'Erreur lors de la suppression de la livraison','msg'=>$e->getMessage()],500);
        }
    }
}
