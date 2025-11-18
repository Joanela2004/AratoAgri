<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Commande;
use App\Models\DetailCommande;
use App\Models\Livraison;
use App\Models\ModePaiement;
use App\Models\Lieu;
use App\Models\Promotion;

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
            'numLieu'             => 'nullable|exists:lieux,numLieu',
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
                $numPromotion = $promotion->numPromotion;
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
                'montantTotal'     => $request->montantTotal,
                'payerLivraison'   => $request->payerLivraison ?? false,
                'codePromo'        => $numPromotion ? $request->codePromo : null,
                'numPromotion'     => $numPromotion,
                'dateCommande'     => now()
            ]);

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

            Livraison::create([
                'numCommande'    => $commande->numCommande,
                'numLieu'        => $request->numLieu,
                'lieuLivraison'  => $request->lieuNom ?? null,
                'transporteur'   => $request->transporteur ?? 'Arato Express',
                'referenceColis' => 'COLIS-' . strtoupper(Str::random(8)),
                'fraisLivraison' => $request->fraisLivraison,
                'contactTransporteur' => $request->contactTransporteur ?? null,
                'dateExpedition' => $request->payerLivraison ? now() : null,
                'dateLivraison'  => null,
                'statutLivraison'=> $request->payerLivraison ? 'en cours' : 'en préparation',
            ]);

            DB::commit();
            return response()->json($commande->load(['detailCommandes.produit','livraisons','modePaiement','lieu','promotion']), 201);

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
