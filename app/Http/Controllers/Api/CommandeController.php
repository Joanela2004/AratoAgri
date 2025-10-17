<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commande;
use App\Models\Panier;
use App\Models\DetailCommande;
use App\Models\Paiement;
use App\Models\Livraison;
use App\Models\ModePaiement;
use App\Models\FraisLivraison;

class CommandeController extends Controller
{
    // Liste des commandes du client connecté
    public function indexClient()
    {
        $commandes = Commande::with(['detailCommandes.produit','livraison','paiement','mode_paiement'])
            ->where('numUtilisateur', auth()->id())
            ->get();
        return response()->json($commandes, 200);
    }

    // Afficher une commande spécifique du client
    public function showClient($id)
    {
        $commande = Commande::with(['detailCommandes.produit','livraison','paiement','mode_paiement'])
            ->where('numUtilisateur', auth()->id())
            ->findOrFail($id);
        return response()->json($commande, 200);
    }

    // Liste de toutes les commandes (admin)
    public function index()
    {
        $commandes = Commande::with(['utilisateur','detailCommandes.produit','livraison','paiement','mode_paiement'])
            ->get();
        return response()->json($commandes, 200);
    }

    // Créer une commande à partir du panier
    public function store(Request $request)
    {
        $request->validate([
            'numModePaiement'=>'required|exists:mode_paiements,numModePaiement',
            'adresseDeLivraison'=>'required|string|max:255',
            'payerLivraison' => 'boolean',
            'statut'=>'required|in:en cours,récu'
        ]);

        $userId = auth()->id();
        $panier = Panier::with('detailPaniers.produit')->where('numUtilisateur', $userId)->first();

        if (!$panier || $panier->detailPaniers->isEmpty()) {
            return response()->json(['error'=>'Panier vide ou inexistant'],400);
        }

        $montantProduit = $panier->detailPaniers->sum(fn($i) => $i->produit->prix * $i->poids);
        $poidsTotal = $panier->detailPaniers->sum('poids');

        $fraisLivraison = FraisLivraison::where('poidsMin','<=',$poidsTotal)
            ->where('poidsMax','>=',$poidsTotal)
            ->first();

        if(!$fraisLivraison){
            return response()->json(['error'=>'Aucun tarif de livraison pour ce poids'],400);
        }

        $montantfraisLivraison = $fraisLivraison->frais;
        $payerLivraison = $request->boolean('payerLivraison', false);
        $montantTotal = $payerLivraison ? $montantProduit + $montantfraisLivraison : $montantProduit;

        $modePaiement = ModePaiement::findOrFail($request->numModePaiement);
        if ($modePaiement->solde < $montantProduit) {
            return response()->json(['error'=>'Solde insuffisant'],401);
        }

        $modePaiement->solde -= $montantProduit;
        $modePaiement->save();
        $statutPaiement = 'effectué';

        $commande = Commande::create([
            'numUtilisateur'=>$userId,
            'numModePaiement'=>$request->numModePaiement,
            'dateCommande'=>now(),
            'statut'=>$request->statut,
            'montantTotal'=>$montantTotal,
            'adresseDeLivraison'=>$request->adresseDeLivraison,
            'payerLivraison'=>$payerLivraison
        ]);

        foreach ($panier->detailPaniers as $detailPanier) {
            DetailCommande::create([
                'numCommande' => $commande->numCommande,
                'numProduit' => $detailPanier->numProduit,
                'poids' => $detailPanier->poids,
                'prixUnitaire' => $detailPanier->produit->prix,
                'sousTotal' => $detailPanier->produit->prix * $detailPanier->poids,
            ]);
        }

        Paiement::create([
            'numCommande'=>$commande->numCommande,
            'numModePaiement'=>$request->numModePaiement,
            'montantApayer'=>$montantTotal,
            'statut'=>$statutPaiement,
            'datePaiement'=>now()
        ]);

        Livraison::create([
            'numCommande'=>$commande->numCommande,
            'lieuLivraison'=>$commande->adresseDeLivraison,
            'fraisLivraison'=>$montantfraisLivraison,
        ]);

        $panier->delete();

        return response()->json($commande->load(['utilisateur','detailCommandes.produit','livraison','paiement','mode_paiement']),201);
    }

    // Afficher une commande par ID (admin)
    public function show(string $id)
    {
        $commande = Commande::with(['utilisateur','detailCommandes.produit','livraison','paiement','mode_paiement'])
            ->findOrFail($id);
        return response()->json($commande, 200);
    }

    // Mise à jour d'une commande par le client
    public function updateClient(Request $request, string $id)
    {
        $commande = Commande::where('numUtilisateur', auth()->id())->findOrFail($id);
        $livraison = $commande->livraison;

        if (in_array($livraison->statutLivraison, ['livré(e)s'])) {
            return response()->json(['error'=>'Commande déjà livrée, modification impossible'],403);
        }

        $request->validate([
            'adresseDeLivraison'=>'sometimes|string|max:255',
            'payerLivraison'=>'sometimes|boolean'
        ]);

        if ($request->boolean('payerLivraison') && !$commande->payerLivraison) {
            $modePaiement = $commande->mode_paiement;
            $frais = $livraison->fraisLivraison;

            if ($modePaiement->solde < $frais) {
                return response()->json(['error'=>'Solde insuffisant pour payer la livraison'],401);
            }

            $modePaiement->solde -= $frais;
            $modePaiement->save();
            $commande->payerLivraison = true;
            $livraison->update(['statutLivraison'=>'en cours']);
        }

        $commande->update($request->only(['adresseDeLivraison','payerLivraison']));
        return response()->json($commande->load(['detailCommandes.produit','livraison','paiement','mode_paiement']),200);
    }

    // Supprimer une commande
    public function destroy(string $id)
    {
        $commande = Commande::where('numUtilisateur', auth()->id())->findOrFail($id);
        $commande->delete();
        return response()->json(['message'=>'Commande supprimée'],200);
    }
}
