<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Commande;
use App\Models\DetailCommande;
use App\Models\Livraison;
use App\Models\LieuLivraison;
use App\Models\Produit;
use App\Models\Paiement;
use App\Http\Controllers\Api\AuthController; // Pour la fusion panier

class CommandeController extends Controller
{
    // === Liste des commandes client ===
    public function indexClient()
    {
        $commandes = Commande::with([
            'detailCommandes.produit',
            'livraisons',
            'modePaiement',
            'lieu',
            'promotion'
        ])
        ->where('numUtilisateur', auth()->id())
        ->latest()
        ->get();

        return response()->json($commandes);
    }
public function index()
{
    $commandes = Commande::with([
            'utilisateur',
            'detailCommandes.produit',
            'livraisons',
            'modePaiement',
            'lieu'
        ])
        ->latest()
        ->get();

    return response()->json($commandes);
}
    public function showClient($id)
    {
        $commande = Commande::with([
            'detailCommandes.produit',
            'livraisons',
            'modePaiement',
            'lieu',
            'promotion'
        ])
        ->where('numUtilisateur', auth()->id())
        ->findOrFail($id);

        return response()->json($commande);
    }


public function show($numCommande)
{
    $commande = Commande::with([
        'utilisateur',
        'detailCommandes.produit.categorie',
        'livraisons',
        'modePaiement',
        'lieu',
        'promotion'
    ])->findOrFail($numCommande);

    return response()->json($commande);
}
    public function store(Request $request)
    {
        $userId = auth()->id();

        $request->validate([
            'numModePaiement' => 'nullable|exists:mode_Paiements,numModePaiement',
            'numLieu'         => 'nullable|exists:lieux_livraison,numLieu',
            'lieuNom'         => 'nullable|string|max:255',
            'payerLivraison'  => 'boolean',
            'sousTotal'       => 'required|numeric|min:0',
            'fraisLivraison'  => 'required|numeric|min:0',
            'montantTotal'    => 'required|numeric|min:0',
            'codePromo'       => 'nullable|string|max:50',
            'panier'          => 'required|array|min:1',
            'panier.*.numProduit' => 'required|exists:produits,numProduit',
            'panier.*.prix'       => 'required|numeric|min:0',
            'panier.*.poids'      => 'required|numeric|min:0.01',
            'panier.*.decoupe'    => 'nullable|string',
        ]);
       
        $panier = $request->panier;
        if (empty($panier)) {
            $panier = AuthController::recupererPanierFusionne($userId);
            if (empty($panier)) {
                return response()->json(['message' => 'Votre panier est vide.'], 422);
            }
        }

        // Vérification stock (on regarde, mais on ne touche PAS encore)
        foreach ($panier as $item) {
            $produit = Produit::find($item['numProduit']);
            if ($produit->poids < $item['poids']) {
                return response()->json([
                    'message' => "Stock insuffisant pour {$produit->nomProduit}",
                    'disponible' => $produit->poids . ' kg',
                    'demandé' => $item['poids'] . ' kg'
                ], 422);
            }
        }

        // Gestion code promo (tu peux garder ton code existant ici)
        $montantFinal = $request->montantTotal;

        DB::beginTransaction();
        try {
            $reference = 'CMD-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);


            $commande = Commande::create([
                'numUtilisateur'    => $userId,
                'numModePaiement'   => $request->numModePaiement,
                'numLieu'           => $request->numLieu,
                'lieuNom'           => $request->lieuNom,
                'statut'            => 'en attente',
                'sousTotal'         => $request->sousTotal,
                'fraisLivraison'    => $request->fraisLivraison,
                'montantTotal'      => $montantFinal,
                'payerLivraison'    => $request->payerLivraison ?? false,
                'codePromo'         => $request->codePromo ?? null,
                'dateCommande'      => now(),
                'referenceCommande' => $reference,
            ]);
    
    Paiement::create([
    'numCommande'   => $commande->numCommande,
    'numModePaiement' => $request->numModePaiement,
    'montantApayer' => $montantFinal,
    'statut'        => 'en attente',
    'datePaiement'  => null
]);
            foreach ($panier as $item) {
                DetailCommande::create([
                    'numCommande'   => $commande->numCommande,
                    'numProduit'    => $item['numProduit'],
                    'poids'         => $item['poids'],
                    'decoupe'       => $item['decoupe'] ?? null,
                    'prixUnitaire'  => $item['prix'],
                    'sousTotal'     => $item['prix'] * $item['poids'],
                ]);
            }
           

            $nomLieu = $request->lieuNom;
            if (!$nomLieu && $request->numLieu) {
                $nomLieu = LieuLivraison::find($request->numLieu)?->nomLieu ?? 'Lieu inconnu';
            }

            Livraison::create([
                'numCommande'     => $commande->numCommande,
                'lieuLivraison'   => $nomLieu,
                'fraisLivraison'  => $request->fraisLivraison,
                'statutLivraison' =>'en cours',
                'referenceColis'  => 'LV-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            ]);
                   
            // Nettoyer le panier fusionné
            AuthController::viderPanierFusionne($userId);

            DB::commit();

            return response()->json([
                'message' => 'Commande créée avec succès !',
                'commande' => $commande->load(['detailCommandes.produit', 'livraisons', 'modePaiement', 'lieu'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création de la commande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function update(Request $request, $id)
{
    $commande = Commande::with('detailCommandes.produit')->findOrFail($id);
    $ancienStatut = $commande->statut;

    $request->validate([
        'statut' => 'sometimes|in:en attente,validée,payée,expédiée,annulée,livrée',
        'payerLivraison' => 'sometimes|boolean',
        'estConsulte' => 'sometimes|boolean',
    ]);

    if ($request->has('estConsulte')) {
        $commande->estConsulte = $request->estConsulte;
    }

    if ($request->has('statut')) {
        $nouveauStatut = $request->statut;

        if (in_array($nouveauStatut, ['validée', 'payée']) && !in_array($ancienStatut, ['validée', 'payée'])) {
            foreach ($commande->detailCommandes as $detail) {
                Produit::where('numProduit', $detail->numProduit)->decrement('poids', $detail->poids);
            }
        }

        if ($nouveauStatut === 'annulée' && $ancienStatut !== 'annulée') {
            foreach ($commande->detailCommandes as $detail) {
                Produit::where('numProduit', $detail->numProduit)->increment('poids', $detail->poids);
            }
        }

        $commande->statut = $nouveauStatut;
    }

    if ($request->has('payerLivraison')) {
        $commande->payerLivraison = $request->payerLivraison;
    }

    $commande->save();

    return response()->json([
        'message' => 'Commande mise à jour',
        'commande' => $commande->load(['detailCommandes.produit', 'livraisons'])
    ]);
}

    // === Annulation par le client (optionnel) ===
    public function destroy($id)
    {
        $commande = Commande::where('numUtilisateur', auth()->id())
                            ->where('statut', 'en attente')
                            ->findOrFail($id);

        // Remettre le stock si jamais il avait été déduit
        foreach ($commande->detailCommandes as $detail) {
            Produit::where('numProduit', $detail->numProduit)
                   ->increment('poids', $detail->poids);
        }

        $commande->delete();

        return response()->json(['message' => 'Commande annulée avec succès']);
    }

    public function confirmerPaiement($numCommande)
{
    $this->authorize('admin'); 

    $paiement = Paiement::where('numCommande', $numCommande)->firstOrFail();
    
    DB::transaction(function () use ($paiement) {
        $paiement->update([
            'statut' => 'effectué',
            'datePaiement' => now()
        ]);

        // Déduire le stock + changer statut commande
        $commande = $paiement->commande;
        $commande->update(['statut' => 'payée']);

        foreach ($commande->detailCommandes as $detail) {
            Produit::where('numProduit', $detail->numProduit)
                   ->decrement('poids', $detail->poids);
        }
    });

    return response()->json(['message' => 'Paiement confirmé – Stock déduit']);
}
}