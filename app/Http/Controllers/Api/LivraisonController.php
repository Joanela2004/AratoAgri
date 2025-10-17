<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Livraison;

class LivraisonController extends Controller
{
    // Liste des livraisons pour le client connecté
    public function indexClient()
    {
        $livraisons = Livraison::with('commande')
            ->whereHas('commande', fn($q) => $q->where('numUtilisateur', auth()->id()))
            ->get();
        return response()->json($livraisons,200);
    }

    // Liste de toutes les livraisons (admin)
    public function index()
    {
        $livraisons = Livraison::with('commande')->get();
        return response()->json($livraisons,200);
    }

    // Afficher une livraison spécifique du client
    public function showClient(string $id)
    {
        $livraison = Livraison::with('commande')
            ->whereHas('commande', fn($q) => $q->where('numUtilisateur', auth()->id()))
            ->findOrFail($id);
        return response()->json($livraison,200);
    }

    // Mise à jour d'une livraison
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

        return response()->json(['message'=>'Livraison mise à jour','livraison'=>$livraison],200);
    }

    // Supprimer une livraison
    public function destroy(string $id)
    {
        $livraison = Livraison::findOrFail($id);
        $livraison->delete();
        return response()->json(['message'=>'Livraison supprimée'],200);
    }
}
