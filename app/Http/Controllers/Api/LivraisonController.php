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
        $livraisons = Livraison::with(['commande.detailCommandes.produit','commande.mode_paiement','commande.lieu'])->get();
        return response()->json($livraisons, 200);
    }

    public function indexClient()
    {
        $livraisons = Livraison::with(['commande.detailCommandes.produit','commande.mode_paiement','commande.lieu'])
            ->whereHas('commande', function($q){
                $q->where('numUtilisateur', auth()->id());
            })
            ->get();
        return response()->json($livraisons, 200);
    }

    public function showClient($id)
    {
        $livraison = Livraison::with(['commande.detailCommandes.produit','commande.mode_paiement','commande.lieu'])
            ->whereHas('commande', function($q){
                $q->where('numUtilisateur', auth()->id());
            })
            ->findOrFail($id);
        return response()->json($livraison, 200);
    }

    // Mettre à jour le statut d'une livraison
    public function updateStatus(Request $request, $id)
    {
        $livraison = Livraison::findOrFail($id);

        $request->validate([
            'statutLivraison' => 'required|in:en préparation,en cours,livré'
        ]);

        DB::beginTransaction();
        try {
            $livraison->statutLivraison = $request->statutLivraison;

            if ($request->statutLivraison === 'en cours' && !$livraison->dateExpedition) {
                $livraison->dateExpedition = now();
            }

            if ($request->statutLivraison === 'livré') {
                $livraison->dateLivraison = now();
            }

            $livraison->save();
            DB::commit();

            return response()->json($livraison->load(['commande.detailCommandes.produit','commande.mode_paiement','commande.lieu']), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error'=>'Erreur lors de la mise à jour de la livraison','msg'=>$e->getMessage()],500);
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
