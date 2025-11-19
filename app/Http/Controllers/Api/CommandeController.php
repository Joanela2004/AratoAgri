<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Commande;
use App\Models\DetailCommande;
use App\Models\Livraison;
use App\Models\Promotion;
use App\Models\LieuLivraison;
class CommandeController extends Controller
{
    public function indexClient()
    {
        $commandes = Commande::with(['detailCommandes.produit','livraisons','modePaiement','lieu','promotion'])
            ->where('numUtilisateur', auth()->id())
            ->get();
        return response()->json($commandes, 200);
    }

    public function showClient($id)
    {
        $commande = Commande::with(['detailCommandes.produit','livraisons','modePaiement','lieu','promotion'])
            ->where('numUtilisateur', auth()->id())
            ->findOrFail($id);
        return response()->json($commande, 200);
    }

    public function index()
    {
        $commandes = Commande::with(['utilisateur','detailCommandes.produit','livraisons','modePaiement','lieu','promotion'])->get();
        return response()->json($commandes, 200);
    }

  public function store(Request $request)
{
    $request->validate([
        'numModePaiement'     => 'required|exists:mode_paiements,numModePaiement',
        'numLieu'             => 'nullable|exists:lieux_livraison,numLieu',
        'payerLivraison'      => 'boolean',
        'statut'              => 'required|string',
        'sousTotal'           => 'required|numeric|min:0',
        'fraisLivraison'      => 'required|numeric|min:0',
        'montantTotal'        => 'required|numeric|min:0',
        'codePromo'           => 'nullable|string',
        'panier'              => 'required|array|min:1',
        'panier.*.numProduit' => 'required|exists:produits,numProduit',
        'panier.*.prix'       => 'required|numeric|min:0',
        'panier.*.poids'      => 'required|numeric|min:0.01',
    ]);

    $userId = auth()->id();
    $panier = $request->panier;

    $numPromotion = null;
    $codePromoApplique = null;
    $montantReduction = 0;

    // Vérifier et appliquer le code promo
    if ($request->filled('codePromo')) {
        $promotion = Promotion::where('codePromo', $request->codePromo)
            ->where('statutPromotion', true)
            ->where(function($q) { 
                $q->whereNull('dateDebut')->orWhere('dateDebut', '<=', now()); 
            })
            ->where(function($q) { 
                $q->whereNull('dateFin')->orWhere('dateFin', '>=', now()); 
            })
            ->first();

        if ($promotion) {
            $dejaUtilise = Commande::where('numUtilisateur', $userId)
                                    ->where('codePromo', $promotion->codePromo)
                                    ->exists();

            if (!$dejaUtilise && $request->sousTotal >= $promotion->montantMinimum) {
                $numPromotion = $promotion->numPromotion;
                $codePromoApplique = $promotion->codePromo;

                if ($promotion->typePromotion === 'Montant fixe') {
                    $montantReduction = $promotion->valeur;
                } elseif ($promotion->typePromotion === 'Pourcentage') {
                    $montantReduction = ($request->sousTotal + $request->fraisLivraison) * ($promotion->valeur / 100);
                }
            }
        }
    }

    // Calcul du montant total après réduction
    $montantTotalApplique = $request->montantTotal - $montantReduction;
    if ($montantTotalApplique < 0) $montantTotalApplique = 0;

    // Récupérer le nom du lieu de livraison si numLieu fourni
    $nomLieu = null;
    if ($request->numLieu) {
        $lieu = LieuLivraison::find($request->numLieu);
        if ($lieu) {
            $nomLieu = $lieu->nomLieu;
        }
    }

    DB::beginTransaction();
    try {
        // Créer la commande
        $commande = Commande::create([
            'numUtilisateur'   => $userId,
            'numModePaiement'  => $request->numModePaiement,
            'numLieu'          => $request->numLieu,
            'statut'           => $request->statut,
            'sousTotal'        => $request->sousTotal,
            'fraisLivraison'   => $request->fraisLivraison,
            'montantTotal'     => $montantTotalApplique,
            'payerLivraison'   => $request->payerLivraison ?? false,
            'codePromo'        => $codePromoApplique,
            'numPromotion'     => $numPromotion,
            'dateCommande'     => now()
        ]);

        // Créer les détails de commande
        foreach ($panier as $item) {
            DetailCommande::create([
                'numCommande'  => $commande->numCommande,
                'numProduit'   => $item['numProduit'],
                'poids'        => $item['poids'],
                'decoupe'      => $item['decoupe'] ?? 'entière',
                'prixUnitaire' => $item['prix'],
                'sousTotal'    => $item['sousTotal'] ?? ($item['prix'] * $item['poids']),
            ]);
        }

        // Créer la livraison
        Livraison::create([
            'numCommande'    => $commande->numCommande,
            'numLieu'        => $request->numLieu,
            'lieuLivraison'  => $request->lieuNom ?? $nomLieu,
            'transporteur'   => $request->transporteur ?? 'Arato Express',
            'referenceColis' => 'COLIS-' . strtoupper(Str::random(8)),
            'fraisLivraison' => $request->fraisLivraison,
            'contactTransporteur' => $request->contactTransporteur ?? null,
            'dateExpedition' => $request->payerLivraison ? now() : null,
            'dateLivraison'  => null,
            'statutLivraison'=> $request->payerLivraison ? 'en cours' : 'en préparation',
        ]);

        DB::commit();
        return response()->json(
            $commande->load(['detailCommandes.produit','livraisons','modePaiement','lieu','promotion']),
            201
        );

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error'=>'Erreur lors de la création de la commande','msg'=>$e->getMessage()],500);
    }
}


    public function updateClient(Request $request, string $id)
    {
        $commande = Commande::where('numUtilisateur', auth()->id())->findOrFail($id);

        $request->validate([
            'statut' => 'sometimes|string',
            'payerLivraison' => 'sometimes|boolean'
        ]);

        if ($request->has('statut')) {
            $commande->statut = $request->statut;
        }

        if ($request->has('payerLivraison')) {
            $commande->payerLivraison = $request->payerLivraison;
        }

        $commande->save();

        return response()->json($commande->load(['detailCommandes.produit','livraisons','modePaiement','lieu','promotion']),200);
    }

    public function destroy(string $id)
    {
        $commande = Commande::where('numUtilisateur', auth()->id())->findOrFail($id);
        $commande->delete();
        return response()->json(['message'=>'Commande supprimée'],200);
    }
}
