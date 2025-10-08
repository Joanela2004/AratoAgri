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

class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $commande = Commande::with(['utilisateur','detailCommandes.produit','livraison','paiement','mode_paiement'])->get();
        return response()->json($commande,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'numUtilisateur'=>'required|exists:utilisateurs,numUtilisateur',
            'numModePaiement'=>'required|exists:mode_paiements,numModePaiement',
            'adresseDeLivraison'=>'required|string|max:255'
        ]);

        //Recuperer le panier actif de l'utilisateur
        $panier = Panier::with('detailPaniers.produit')->where('numUtilisateur',$request->numUtilisateur)->first();
        
        if(!$panier || $panier->detailPaniers->isEmpty()){
            return response()->json(['error'=>'Panier vide ou inexistant'],400);
        }

        //calculons le montant total A partir du panier
        $montantTotal=$panier->detailPaniers->sum(function($i){
            return $i->produit->prix * $i->poids;
        });

        //Recuperer le mode de paiement
        $modePaiement=ModePaiement::find($request->numModePaiement);

        //determions le statut du paiement selon le solde
        $statutPaiement=$modePaiement->solde>=$montantTotal ? 'effectué':'echoué';

        if($statutPaiement==='effectué'){
            $modePaiement->solde-=$montantTotal;
            $modePaiement->save();
        }

        // creer commande
        $commande = Commande::create([
            'numUtilisateur'=>$request->numUtilisateur,
            'numModePaiement'=>$request->numModePaiement,
            'dateCommande'=>now(),
            'statut'=>$statutPaiement==='effectué' ? 'payée' : 'en attente',
            'montantTotal'=>$montantTotal,
            'adresseDeLivraison'=>$request->adresseDeLivraison
        ]);

       
        //creer les detailsCommandes a partir du panier
       foreach ($panier->detailPaniers as $detailPanier) {
    $prix = $detailPanier->produit->prix ?? 0;
    $poids = $detailPanier->poids ?? 0;
    $sousTotal = $prix * $poids;

    DetailCommande::create([
        'numCommande' => $commande->numCommande,
        'numProduit' => $detailPanier->numProduit,
        'poids' => $poids,
        'prixUnitaire' => $prix,
        'sousTotal' => $sousTotal,
    ]);
}


      
        //creer un paiement associé
        Paiement::create([
            'numCommande'=>$commande->numCommande,
            'numModePaiement'=>$request->numModePaiement,
            'montantApayer'=>$montantTotal,
            'statut'=>$statutPaiement,
            'datePaiement'=>now()
        ]);

        // creer la livraison 
        Livraison::create([
            'numCommande'=>$commande->numCommande,
            'modeLivraison'=>'Ville',
            'transporteur'=>'à determiner',
            'dateExpedition' => null,
            'dateLivraison' => null,
            'statutLivraison' => 'en préparation'
        ]);

        //supprimer panier
        $panier->delete();
        return response()->json(
        $commande->load(['utilisateur', 'detailCommandes.produit', 'livraison', 'paiement', 'mode_paiement']),
        201
    );
    }   

    /**
     * Display the specified resource.
     */
    //afficher une commande
    public function show(string $id)
    {
    $commande = Commande::with(['utilisateur','detailCommandes.produit','livraison','paiement','mode_paiement'])
    ->findOrFail($id);
return response()->json($commande, 200);
   
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $commande = Commande::findOrFail($id);
        $request->validate([
            'numUtilisateur'=>'sometimes|exists:utilisateurs,numUtilisateur',
            'statut'=>'sometimes|string|max:50',
            'adresseDeLivraison'=>'sometimes|string|max:100'
        ]);
        $commande->update($request->only(['numUtilisateur','statut','adresseDeLivraison']));
        return response()->json($commande->load([
            'utilisateur','detailCommandes.produit','livraison','paiement','mode_paiement'
        ]),200);
    }

    /**
     * Remove the specified resource from storage.
     */
    //supprimer une commande
    public function destroy(string $id)
    {
        $commande=Commande::findOrFail($id);
        $commande->delete();
        return response()->json(['message'=>'Commande supprimé']);
    }
}
