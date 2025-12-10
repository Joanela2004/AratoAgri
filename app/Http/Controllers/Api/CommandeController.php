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
use App\Models\Promotion;
use App\Http\Controllers\Api\AuthController; // Pour la fusion panier

class CommandeController extends Controller
{
    
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
        'numLieu' => 'nullable|exists:lieux_livraison,numLieu',
        'lieuNom' => 'nullable|string|max:255',
        'payerLivraison' => 'boolean',
        'sousTotal' => 'required|numeric|min:0',
        'fraisLivraison' => 'required|numeric|min:0',
        'montantTotal' => 'required|numeric|min:0',
        'codePromo' => 'nullable|string|max:50',
        'panier' => 'required|array|min:1',
        'panier.*.numProduit' => 'required|exists:produits,numProduit',
        'panier.*.prix' => 'required|numeric|min:0',
        'panier.*.poids' => 'required|numeric|min:0.01',
        'panier.*.decoupe' => 'nullable|string',
    ]);

    $panier = $request->panier;
    if (empty($panier)) {
        $panier = AuthController::recupererPanierFusionne($userId);
        if (empty($panier)) {
            return response()->json(['message' => 'Votre panier est vide.'], 422);
        }
    }

    // Vérification stock
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

    $sousTotal = $request->sousTotal;
    $montantFinal = $sousTotal; 
    $numPromotion = null;
    $codePromoUtilise = null;

    $promotionAuto = Promotion::where('automatique', true)
        ->where('statutPromotion', true)
        ->where('dateDebut', '<=', now())
        ->where('dateFin', '>=', now())
        ->where('montantMinimum', '<=', $sousTotal)
        ->first();

    if ($promotionAuto) {
        $reduction = $promotionAuto->typePromotion === 'Pourcentage'
            ? $sousTotal * ($promotionAuto->valeur / 100)
            : $promotionAuto->valeur;

        $reduction = round($reduction / 100) * 100; // Arrondi au 100 Ar près
        $montantFinal = $sousTotal - $reduction;
        $numPromotion = $promotionAuto->numPromotion;
    }
    // 2. Sinon, code promo manuel ?
    elseif ($request->filled('codePromo')) {
        $validation = app(PromotionController::class)->valider($request);
        if ($validation->getStatusCode() !== 200) {
            return $validation; // Retourne l'erreur du contrôleur
        }

        $result = json_decode($validation->getContent(), true);
        $montantFinal = $result['montantAPayer'];
        $promotion = Promotion::where('codePromo', $request->codePromo)->firstOrFail();
        $numPromotion = $promotion->numPromotion;
        $codePromoUtilise = $request->codePromo;

        // Marquer le code comme utilisé (si tu veux)
        PromotionUtilisateur::where('numPromotion', $numPromotion)
            ->where('numUtilisateur', $userId)
            ->update(['statut' => 'utilisé']);
    }

    // Montant total final (avec frais de livraison)
    $montantTotalAvecLivraison = $montantFinal + $request->fraisLivraison;

    DB::beginTransaction();
    try {
        $reference = 'CMD-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $commande = Commande::create([
            'numUtilisateur' => $userId,
            'numModePaiement' => $request->numModePaiement,
            'numLieu' => $request->numLieu,
            'lieuNom' => $request->lieuNom,
            'statut' => 'en attente',
            'sousTotal' => $sousTotal,
            'fraisLivraison' => $request->fraisLivraison,
            'montantTotal' => $montantTotalAvecLivraison,
            'payerLivraison' => $request->payerLivraison ?? false,
            'codePromo' => $codePromoUtilise,
            'numPromotion' => $numPromotion, // Lien avec la promo
            'dateCommande' => now(),
            'referenceCommande' => $reference,
        ]);

        // Créer les détails
        foreach ($panier as $item) {
            DetailCommande::create([
                'numCommande' => $commande->numCommande,
                'numProduit' => $item['numProduit'],
                'poids' => $item['poids'],
                'decoupe' => $item['decoupe'] ?? null,
                'prixUnitaire' => $item['prix'],
                'sousTotal' => $item['prix'] * $item['poids'],
            ]);
        }

        // Livraison
        $nomLieu = $request->lieuNom;
        if (!$nomLieu && $request->numLieu) {
            $nomLieu = LieuLivraison::find($request->numLieu)?->nomLieu ?? 'Lieu inconnu';
        }

        Livraison::create([
            'numCommande' => $commande->numCommande,
            'lieuLivraison' => $nomLieu,
            'fraisLivraison' => $request->fraisLivraison,
            'statutLivraison' => 'en cours',
            'referenceColis' => 'LV-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
        ]);

        Paiement::create([
            'numCommande' => $commande->numCommande,
            'numModePaiement' => $request->numModePaiement,
            'montantApayer' => $montantTotalAvecLivraison,
            'statut' => 'en attente',
        ]);

        // Vider le panier
        AuthController::viderPanierFusionne($userId);

        DB::commit();

        return response()->json([
            'message' => 'Commande créée avec succès !',
            'commande' => $commande->load(['detailCommandes.produit', 'livraisons', 'modePaiement', 'lieu', 'promotion'])
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Erreur création commande', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Erreur lors de la création'], 500);
    }
}
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

        $commande = $paiement->commande;
        $commande->update(['statut' => 'payée']);

        foreach ($commande->detailCommandes as $detail) {
            Produit::where('numProduit', $detail->numProduit)
                   ->decrement('poids', $detail->poids);
        }
    });

    return response()->json(['message' => 'Paiement confirmé – Stock déduit']);
}
public function updateModePaiement(Request $request, $referenceCommande)
{
    $request->validate([
        'numModePaiement' => 'required|exists:mode_Paiements,numModePaiement'
    ]);

    $commande = Commande::where('referenceCommande', $referenceCommande)
                        ->where('numUtilisateur', auth()->id())
                        ->firstOrFail();
    DB::transaction(function () use ($commande, $request) {
        $commande->update([
            'numModePaiement' => $request->numModePaiement
        ]);

                Paiement::where('numCommande', $commande->numCommande)
                ->update(['numModePaiement' => $request->numModePaiement]);
    });

    return response()->json([
        'message' => 'Mode de paiement mis à jour avec succès',
        'mode_paiement' => $commande->modePaiement?->nomModePaiement
    ]);
}
}