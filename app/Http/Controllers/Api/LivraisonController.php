<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Livraison;
class LivraisonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexClient(){
        $livraisons=Livraison::with('commande')->whereHas('commande',function($a){
            $a->where('numUtilisateur',auth()->id());
        })->get();
        return response()->json($livraisons,200);
    }

    public function index()
    {
        $livraison = Livraison::with('commande')->get();
        return response()->json($livraison,200);
    }

   
    public function showClient(string $id)
    {
        $livraison=Livraison::with('commande')->whereHas('commande',function($a){$a->where('numUtilisateur',auth()->id());})->findOrFail($id);
        return response()->json($livraison,200);
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request, string $id)
    {
        $livraison = Livraison::findOrFail($id);

        $request->validate([
            'statutLivraison' => 'required|string|in:en préparation,en cours,livré(e)s,annulée',
            'transporteur' => 'sometimes|string|max:100',
            'contactTransporteur' => 'sometimes|string|max:20',
            'referenceColis' => 'sometimes|string|max:50',
            'lieuLivraison'=>'sometimes|string|max:100',
            'fraisLivraison'=>'sometimes|numeric'
        ]);

        
        $livraison->update([
            'statutLivraison' => $request->statutLivraison,
            'dateExpedition' => $request->statutLivraison === 'en cours' && !$livraison->dateExpedition ? now() : $livraison->dateExpedition,
            'dateLivraison' => $request->statutLivraison === 'livré(e)s' && !$livraison->dateLivraison ? now() : $livraison->dateLivraison,
            'transporteur' => $request->input('transporteur', $livraison->transporteur),
            'contactTransporteur' => $request->input('contactTransporteur', $livraison->contactTransporteur),
            'referenceColis' => $request->input('referenceColis', $livraison->referenceColis),
            'lieuLivraison' => $request->input('lieuLivraison', $livraison->lieuLivraison),
            'fraisLivraison' => $request->input('fraisLivraison', $livraison->fraisLivraison),
        ]);

        return response()->json([
            'message' => 'Statut de livraison mis à jour avec succès',
            'livraison' => $livraison
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $livraison = Livraison::findOrFail($id);
        $livraison->delete();
        return response()->json(['message'=>'Livraison supprimée'],200);
    }
}
