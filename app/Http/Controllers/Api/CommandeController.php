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
use App\Models\Produit; 

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
    
    public function show($id)
    {
            $commande = Commande::where('numCommande', $id)
                            ->with(['utilisateur', 'detailCommandes','detailCommandes.produit' ,'livraisons.lieu'
                            ]) 
                            ->first(); 

        if (!$commande) {
            return response()->json(['message' => 'Commande introuvable.'], 404);
        }

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
            'numModePaiement'      => 'required|exists:mode_Paiements,numModePaiement',
            'numLieu'              => 'nullable|exists:lieux_livraison,numLieu',
            'payerLivraison'       => 'boolean',
            'statut'               => 'required|string',
            'sousTotal'            => 'required|numeric|min:0',
            'fraisLivraison'       => 'required|numeric|min:0',
            'montantTotal'         => 'required|numeric|min:0',
            'codePromo'            => 'nullable|string',
            'panier'               => 'required|array|min:1',
            'panier.*.numProduit'  => 'required|numeric',
            'panier.*.prix'        => 'required|numeric|min:0',
            'panier.*.poids'       => 'required|numeric|min:0.01',
        ]);

        $userId = auth()->id();
        $panier = $request->panier;

              $produitsToUpdate = [];
        foreach ($panier as $item) {
            $produit = Produit::find($item['numProduit']); // 👈 Récupération du produit
            
            if (!$produit) {
                return response()->json([
                    "message" => "Le produit avec l’ID {$item['numProduit']} n’existe plus.",
                    "solution" => "Veuillez vider ou rafraîchir votre panier."
                ], 422);
            }
            if ($produit->poids < $item['poids']) {
                return response()->json([
                    "message" => "Stock insuffisant pour le produit {$produit->nomProduit}. Disponible: {$produit->poids} kg.",
                    "solution" => "Veuillez réduire la quantité commandée."
                ], 422);
            }

            $produitsToUpdate[$item['numProduit']] = $produit;
        }

        $numPromotion = null;
        $codePromoApplique = null;
        $montantReduction = 0;

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

        $montantTotalApplique = $request->montantTotal - $montantReduction;
        if ($montantTotalApplique < 0) $montantTotalApplique = 0;

        $nomLieu = null;
        if ($request->numLieu) {
            $lieu = LieuLivraison::find($request->numLieu);
            if ($lieu) {
                $nomLieu = $lieu->nomLieu;
            }
        }

        DB::beginTransaction();
        try {
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

            foreach ($panier as $item) {
                // Création du détail de commande
                DetailCommande::create([
                    'numCommande'  => $commande->numCommande,
                    'numProduit'   => $item['numProduit'],
                    'poids'        => $item['poids'],
                    'decoupe'      => $item['decoupe'],
                    'prixUnitaire' => $item['prix'],
                    'sousTotal'    => $item['sousTotal'] ?? ($item['prix'] * $item['poids']),
                ]);
               $produit = $produitsToUpdate[$item['numProduit']]; 
                $produit->poids -= $item['poids']; 
                $produit->save(); 
            }

            Livraison::create([
                'numCommande'    => $commande->numCommande,
                'lieuLivraison'  => $request->lieuNom ?? $nomLieu,
                'transporteur'   => $request->transporteur ?? null,
                'referenceColis' => 'COLIS-' . strtoupper(Str::random(8)),
                'fraisLivraison' => $request->fraisLivraison,
                'contactTransporteur' => $request->contactTransporteur ?? null,
                'dateExpedition' => $request->payerLivraison ? now() : null,
                'dateLivraison'  => null,
                'statutLivraison'=>  'en cours' ,
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



public function update(Request $request, $id)
{
    try {
        $commande = Commande::findOrFail($id);
        
        if ($request->has('statut')) {
            $commande->statut = $request->statut;
        }

             if ($request->has('estConsulte')) {
            $commande->estConsulte = $request->estConsulte; 
        }
        
        $commande->save();
        
        return response()->json($commande, 200);

    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur de mise à jour.'], 500);
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