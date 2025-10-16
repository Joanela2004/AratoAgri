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
    /**
     * Display a listing of the resource.
     */
    public function  indexClient(){

        $commande = Commande::with([
            'detailCommandes.produit','livraison','paiement','mode_paiement'
        ])->where('numUtilisateur',auth()->id())->get();
        return response()->json($commande,200);
    }

    //afficher une commande specifique du client
    public function showClient($id){
        $commande=Commande::with(['detailCommandes.produit','livraison','paiement','mode_paiement'])->where('numUtilisateur',auth()->id())->findOrFail($id);
        return response()->json($commande,200);
    }

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
            'adresseDeLivraison'=>'required|string|max:255',
            'payerLivraison' => 'boolean',
            'statut'=>'required|in:en cours,récu'
        ]);

        //Recuperer le panier actif de l'utilisateur
        $panier = Panier::with('detailPaniers.produit')->where('numUtilisateur',$request->numUtilisateur)->first();
        
        if(!$panier || $panier->detailPaniers->isEmpty()){
            return response()->json(['error'=>'Panier vide ou inexistant'],400);
        }

        //calculons le montant et poids total  A partir du panier
    
        $montantProduit=$panier->detailPaniers->sum(function($i){
            return $i->produit->prix * $i->poids;
        });

        $poidsTotal = $panier->detailPaniers->sum('poids');
            
        //cherchons  le frais selon le poids

        $fraisLivraison=FraisLivraison::where('poidsMin','<=',$poidsTotal)
            ->where('poidsMax','>=',$poidsTotal)
            ->first();
            if(!$fraisLivraison){
                return response()->json(['error'=>'Aucun tarif de livraison pour ce poids'],400);
            }
        $montantfraisLivraison=$fraisLivraison->frais;
        $payerLivraison = $request->has('payerLivraison');

        //Montant A payer si le payerLivraison est true
        $montantTotal = $payerLivraison ? $montantProduit + $montantfraisLivraison : $montantProduit;
       
        //Recuperons le mode de paiement
        $modePaiement=ModePaiement::find($request->numModePaiement);


        if($modePaiement->solde>=$montantTotal){
            $modePaiement->solde-=$montantTotal;
            $modePaiement->save();
            $statutPaiement= 'effectué';
        }else if($modePaiement->solde < $montantTotal && $modePaiement->solde>=$montantProduit){
            $modePaiement->solde -= $montantProduit;
            $modePaiement->save();
            $statutPaiement='effectué';
            
        }else{
            return response()->json(['message' => 'Solde insuffisant pour payer les produits'], 401);

        }

        // creer commande
        $commande = Commande::create([
            'numUtilisateur'=>$request->numUtilisateur,
            'numModePaiement'=>$request->numModePaiement,
            'dateCommande'=>now(),
            'statut'=>$request->statut,
            'montantTotal'=>$montantTotal,
            'adresseDeLivraison'=>$request->adresseDeLivraison,
            'payerLivraison'=>$payerLivraison
        ]);

       
        //creons les detailsCommandes a partir du panier
       foreach ($panier->detailPaniers as $detailPanier) {
        $numProduit=$detailPanier->numProduit;
        $prix = $detailPanier->produit->prix;
        $poids = $detailPanier->poids;
        $sousTotal = $prix * $poids;

        DetailCommande::create([
            'numCommande' => $commande->numCommande,
            'numProduit' => $numProduit,
            'poids' => $poids,
            'prixUnitaire' => $prix,
            'sousTotal' => $sousTotal,
        ]);

}
        //creer un paiement 
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
            'lieuLivraison'=>$commande->adresseDeLivraison,
            'fraisLivraison'=>$montantfraisLivraison,
           
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

    
    public function updateClient(Request $request, string $id)
    {
        $commande = Commande::findOrFail($id);
        $request->validate([
            'payerLivraison' => 'sometimes|boolean',
            'adresseDeLivraison'=>'sometimes|string|max:100'
        ]);
     $livraison = $commande->livraison;
        
        if(in_array($livraison->statutLivraison,['livré(e)s'])){
             return response()->json([
            'error' => 'Vous ne pouvez plus modifier cette commande.'
        ], 403);
        }
    // si l utilisateur decide de payerLivraison
    if($request->has('payerLivraison') && $request->boolean('payerLivraison') && !$commande->payerLivraison){
        $modePaiement = $commande->mode_paiement;
        $frais=$livraison->fraisLivraison;
        if(!$livraison){
            return response()->json(['error','livraison introuvable']);
        }
        $frais = $livraison->fraisLivraison;
        if($modePaiement->solde>=$frais){
            $modePaiement->solde-=$frais;
            $modePaiement->save();
            $commande->payerLivraison = true;
            $livraison->update(['statutLivraison'=>'en cours']);
            $commande->payerLivraison=true;
        }else{
            return response()->json(['error'=>'solde insuffisant pour payer la livraison'],401);
        }

    }

        $commande->update($request->only(['adresseDeLivraison','payerLivraison']));
        return response()->json($commande->load([
            'utilisateur','detailCommandes.produit','livraison','paiement','mode_paiement'
        ]),200);
    }

    public function update(Request $request,string $id){
        $commande = Commande::with('livraison','mode_paiement','paiement')->findOrFail($id);
        $request->validate([
            'statut'=>'sometimes|string|max:50',
            'adresseDeLivraison'=>'sometimes|string|max:255',
            'payerLivraison'=>'sometimes|boolean'
        ]);
        if($request->boolean('payerLivraison') && !$commande->payerLivraison){
            $livraison = $commande->livraison;
        $modePaiement = $commande->mode_paiement;
        $paiement = $commande->paiement;
         if (!$livraison) {
            return response()->json(['error' => 'Livraison introuvable'], 404);
        }

        $commande->payerLivraison=true;
        $commande->save();
      
        $paiement = $commande->paiement;
        if($paiement){
            $paiement->update([
                'montantApayer'=>$commande->montantTotal,
                'statut'=>'payé',
                'datePaiement'=>now()
            ]);
        }
        else{
             $paiement = Paiement::create([
                'numCommande' => $commande->numCommande,
                'numModePaiement' => $modePaiement->numModePaiement, 
                'montantApayer' => $commande->montantTotal,
                'statut' => 'payé',
                'datePaiement' => now(),
            ]);
        }
          
        
        }
    
     $commande->update($request->only(['statut', 'adresseDeLivraison']));

    return response()->json(
        $commande->load(['utilisateur', 'detailCommandes.produit', 'livraison', 'paiement', 'mode_paiement']),
        200
    );
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
