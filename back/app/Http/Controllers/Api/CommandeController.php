<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Commande;
use App\Models\DetailCommande;
use App\Models\Livraison;
use App\Models\LieuLivraison;
use App\Models\Produit;
use App\Models\Paiement;
use App\Models\Promotion;
use App\Models\PromotionUtilisateur;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PromotionController;
use App\Mail\CommandeAnnuleeStockMail;

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
            'dateLivraisonSouhaitee' => 'required|date|after_or_equal:today',
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
            $reduction = round($reduction / 100) * 100;
            $montantFinal = $sousTotal - $reduction;
            $numPromotion = $promotionAuto->numPromotion;
        } elseif ($request->filled('codePromo')) {
            $validation = app(PromotionController::class)->valider($request);
            if ($validation->getStatusCode() !== 200) {
                return $validation;
            }
            $result = json_decode($validation->getContent(), true);
            $montantFinal = $result['montantAPayer'];
            $promotion = Promotion::where('codePromo', $request->codePromo)->firstOrFail();
            $numPromotion = $promotion->numPromotion;
            $codePromoUtilise = $request->codePromo;
            PromotionUtilisateur::where('numPromotion', $numPromotion)
                ->where('numUtilisateur', $userId)
                ->update(['statut' => 'utilisé']);
        }

        $montantTotalAvecLivraison = $montantFinal + $request->fraisLivraison;

        DB::beginTransaction();
        try {
            $reference = 'CMD-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $commande = Commande::create([
                'numUtilisateur' => $userId,
                'numModePaiement' => $request->numModePaiement,
                'dateLivraisonSouhaitee' => $request->dateLivraisonSouhaitee,
                'numLieu' => $request->numLieu,
                'lieuNom' => $request->lieuNom,
                'statut' => 'en attente',
                'sousTotal' => $sousTotal,
                'fraisLivraison' => $request->fraisLivraison,
                'montantTotal' => $montantTotalAvecLivraison,
                'payerLivraison' => $request->payerLivraison ?? false,
                'codePromo' => $codePromoUtilise,
                'numPromotion' => $numPromotion,
                'dateCommande' => now(),
                'referenceCommande' => $reference,
            ]);

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

            AuthController::viderPanierFusionne($userId);

            DB::commit();

            return response()->json([
                'message' => 'Commande créée avec succès !',
                'commande' => $commande->load(['detailCommandes.produit', 'livraisons', 'modePaiement', 'lieu', 'promotion'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création commande', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Erreur lors de la création'], 500);
        }
    }

    public function destroyClient($referenceCommande)
    {
        $commande = Commande::where('referenceCommande', $referenceCommande)
            ->where('numUtilisateur', auth()->id())
            ->firstOrFail();

        if (!in_array(strtolower($commande->statut), ['en attente', 'attente'])) {
            return response()->json(['message' => 'Impossible de modifier une commande déjà payée'], 403);
        }

        $commande->delete();

        return response()->json(['message' => 'Commande annulée avec succès']);
    }

  public function verifierEtExpedier(Request $request, $numCommande)
{
    $commande = Commande::findOrFail($numCommande);

    // Autorise "payée" OU "validée"
    if (!in_array($commande->statut, ['payée', 'validée'])) {
        return response()->json(['message' => 'La commande doit être payée ou validée pour être expédiée.'], 400);
    }

    $paiement = Paiement::where('numCommande', $commande->numCommande)->first();
    $etaitPaye = $paiement && $paiement->statut === 'effectué';

    $produitsManquants = [];

    try {
        DB::transaction(function () use ($commande, $request, &$produitsManquants) {
            $details = DetailCommande::where('numCommande', $commande->numCommande)->get();

            if ($details->isEmpty()) {
                throw new \Exception('Aucun produit dans la commande.');
            }

            foreach ($details as $detail) {
                $produit = Produit::findOrFail($detail->numProduit);

                if ($produit->poids < $detail->poids) {
                    $produitsManquants[] = [
                        'nom' => $produit->nomProduit,
                        'poids_demande' => $detail->poids,
                        'poids_disponible' => $produit->poids,
                    ];
                    throw new \Exception('stock_insuffisant');
                }

                $produit->decrement('poids', $detail->poids);
            }

            $livraison = $commande->livraisons()->first();

            if ($livraison) {
                $livraison->update([
                    'transporteur' => $request->transporteur ?? $livraison->transporteur,
                    'contactTransporteur' => $request->contactTransporteur ?? $livraison->contactTransporteur,
                    'referenceColis' => $request->referenceColis ?? $livraison->referenceColis,
                    'lieuLivraison' => $request->lieuLivraison ?? $livraison->lieuLivraison,
                    'statutLivraison' => 'en cours',
                ]);
            }

            $commande->update(['statut' => 'expédiée']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Commande expédiée avec succès.'
        ]);

    } catch (\Exception $e) {
        if ($e->getMessage() === 'stock_insuffisant') {
            DB::transaction(function () use ($commande) {
                $commande->update(['statut' => 'annulée']);
            });

            Mail::to($commande->utilisateur->email)->send(
                new CommandeAnnuleeStockMail($commande, $etaitPaye, $produitsManquants)
            );

            return response()->json([
                'annulee' => true,
                'message' => 'Stock insuffisant. La commande a été annulée automatiquement et le client a été notifié.'
            ]);
        }

        Log::error('Erreur expédition commande #' . $numCommande . ' : ' . $e->getMessage());

        return response()->json([
            'message' => 'Erreur serveur lors de l\'expédition.',
            'error' => $e->getMessage()
        ], 500);
    }
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
        });

        return response()->json(['message' => 'Paiement confirmé – Commande marquée comme payée']);
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

    public function update(Request $request, $numCommande)
    {
        $request->validate([
            'statut' => 'sometimes|required|in:en attente,payée,validée,expédiée,livrée,annulée',
            'payerLivraison' => 'sometimes|boolean',
            'montantTotal' => 'sometimes|numeric|min:0',
            'estConsulte' => 'sometimes|in:0,1',
        ]);

        $commande = Commande::findOrFail($numCommande);

        $ancienStatut = $commande->statut;
        $nouveauStatut = $request->input('statut', $ancienStatut);

        if ($nouveauStatut === 'expédiée' && $ancienStatut !== 'expédiée') {
            if (!in_array($ancienStatut, ['payée', 'validée'])) {
                return response()->json(['message' => 'La commande doit être payée ou validée pour être expédiée.'], 400);
            }

            try {
                DB::transaction(function () use ($commande) {
                    $details = DetailCommande::where('numCommande', $commande->numCommande)->get();

                    if ($details->isEmpty()) {
                        throw new \Exception('Aucun produit trouvé dans cette commande.');
                    }

                    foreach ($details as $detail) {
                        $produit = Produit::findOrFail($detail->numProduit);

                        if ($produit->poids < $detail->poids) {
                            throw new \Exception("Stock insuffisant pour le produit « {$produit->nomProduit} » (ID: {$produit->numProduit}). Stock actuel : {$produit->poids} kg, demandé : {$detail->poids} kg");
                        }

                        $produit->decrement('poids', $detail->poids);
                    }

                    $commande->update(['statut' => 'expédiée']);
                });

                return response()->json([
                    'message' => 'Commande expédiée avec succès. Stock des produits mis à jour.',
                    'commande' => $commande->fresh(['utilisateur', 'modePaiement', 'livraisons'])
                ], 200);

            } catch (\Exception $e) {
                Log::error('Erreur expédition commande #' . $numCommande . ' : ' . $e->getMessage());

                return response()->json([
                    'message' => 'Impossible d\'expédier la commande : ' . $e->getMessage()
                ], 400);
            }
        }

        $updates = $request->only(['statut', 'payerLivraison', 'montantTotal', 'estConsulte']);
        $commande->update($updates);

        return response()->json([
            'message' => 'Commande mise à jour avec succès.',
            'commande' => $commande->fresh(['utilisateur', 'modePaiement', 'livraisons'])
        ], 200);
    }
}