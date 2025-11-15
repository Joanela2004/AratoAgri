<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Commande;
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
        $commandes = Commande::with(['utilisateur','detailCommandes.produit','livraison','paiement','mode_paiement'])->get();

        return response()->json($commandes, 200);
    }

    // Créer une commande depuis le panier envoyé par le front
    public function store(Request $request)
    {
        $request->validate([
            'numModePaiement'       => 'required|exists:mode_paiements,numModePaiement',
            'adresseDeLivraison'    => 'required|string|max:255',
            'payerLivraison'        => 'boolean',
            'statut'                => 'required|in:en cours,récu',
            'panier'                => 'required|array|min:1',
            'panier.*.numProduit'   => 'required|exists:produits,numProduit',
            'panier.*.poids'        => 'required|numeric|min:0.01',
            'panier.*.prix'         => 'required|numeric|min:0'
        ]);

        $userId = auth()->id();
        $panier = $request->panier;

        // Calculs
        $montantProduit = 0;
        $poidsTotal = 0;
        foreach ($panier as $item) {
            $montantProduit += $item['prix'] * $item['poids'];
            $poidsTotal += $item['poids'];
        }

        $fraisLivraison = FraisLivraison::where('poidsMin', '<=', $poidsTotal)
            ->where('poidsMax', '>=', $poidsTotal)
            ->first();

        if (!$fraisLivraison) {
            return response()->json(['error' => 'Aucun tarif de livraison pour ce poids'], 400);
        }

        $montantFrais = $fraisLivraison->frais;
        $payerLivraison = $request->boolean('payerLivraison', false);
        $montantTotal = $montantProduit + ($payerLivraison ? $montantFrais : 0);

        $modePaiement = ModePaiement::findOrFail($request->numModePaiement);

        if ($modePaiement->solde < $montantTotal) {
            return response()->json(['error' => 'Solde insuffisant'], 401);
        }

        DB::beginTransaction();
        try {
            // Débit du compte si paiement total immédiat
            if ($payerLivraison) {
                $modePaiement->solde -= $montantTotal;
                $modePaiement->save();
            }

            // Création commande
            $commande = Commande::create([
                'numUtilisateur'     => $userId,
                'numModePaiement'    => $request->numModePaiement,
                'dateCommande'       => now(),
                'statut'             => $request->statut,
                'montantTotal'       => $montantTotal,
                'adresseDeLivraison' => $request->adresseDeLivraison,
                'payerLivraison'     => $payerLivraison
            ]);

            // Création des détails de commande
            foreach ($panier as $item) {
                DetailCommande::create([
                    'numCommande'  => $commande->numCommande,
                    'numProduit'   => $item['numProduit'],
                    'poids'        => $item['poids'],
                    'decoupe'      => $item['decoupe'] ?? 'entière',
                    'prixUnitaire' => $item['prix'],
                    'sousTotal'    => $item['prix'] * $item['poids'],
                ]);
            }

            // Paiement initial (si frais livré maintenant)
            if ($payerLivraison) {
                Paiement::create([
                    'numCommande'     => $commande->numCommande,
                    'numModePaiement' => $request->numModePaiement,
                    'montantApayer'   => $montantTotal,
                    'statut'          => 'effectué',
                    'datePaiement'    => now(),
                ]);
            }

            // Création livraison
            Livraison::create([
                'numCommande'        => $commande->numCommande,
                'lieuLivraison'      => $request->adresseDeLivraison,
                'transporteur'       => 'Arato Express',
                'referenceColis'     => 'COLIS-' . strtoupper(Str::random(8)),
                'fraisLivraison'     => $montantFrais,
                'contactTransporteur'=> null,
                'dateExpedition'     => $payerLivraison ? now() : null,
                'dateLivraison'      => null,
                'statutLivraison'    => $payerLivraison ? 'en cours' : 'en préparation',
            ]);

            DB::commit();

            return response()->json($commande->load(['detailCommandes.produit','livraison','paiement','mode_paiement']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la création de la commande', 'msg' => $e->getMessage()], 500);
        }
    }

    // Afficher une commande par ID (admin)
    public function show(string $id)
    {
        $commande = Commande::with(['utilisateur','detailCommandes.produit','livraison','paiement','mode_paiement'])
            ->findOrFail($id);

        return response()->json($commande, 200);
    }

    // Mise à jour d'une commande par le client (paiement du frais de livraison après commande)
    public function updateClient(Request $request, string $id)
    {
        $commande = Commande::where('numUtilisateur', auth()->id())->findOrFail($id);
        $livraison = $commande->livraison;
        $modePaiement = $commande->mode_paiement;

        $request->validate([
            'adresseDeLivraison' => 'sometimes|string|max:255',
            'payerLivraison'     => 'sometimes|boolean'
        ]);

        // Paiement du frais de livraison après commande
        if ($request->boolean('payerLivraison') && !$commande->payerLivraison) {
            $frais = $livraison->fraisLivraison;

            if ($modePaiement->solde < $frais) {
                return response()->json(['error'=>'Solde insuffisant pour payer la livraison'],401);
            }

            DB::beginTransaction();
            try {
                // Débit du compte
                $modePaiement->solde -= $frais;
                $modePaiement->save();

                // Marquer la livraison comme payée et en cours
                $commande->payerLivraison = true;
                $commande->save();

                $livraison->update([
                    'statutLivraison' => 'en cours',
                    'dateExpedition'  => now()
                ]);

                // Créer un paiement supplémentaire pour le frais de livraison
                Paiement::create([
                    'numCommande'     => $commande->numCommande,
                    'numModePaiement' => $modePaiement->numModePaiement,
                    'montantApayer'   => $frais,
                    'statut'          => 'effectué',
                    'datePaiement'    => now(),
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur lors du paiement du frais de livraison', 'msg' => $e->getMessage()], 500);
            }
        }

        // Mise à jour de l'adresse si nécessaire
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
