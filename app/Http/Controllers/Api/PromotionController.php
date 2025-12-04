<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Utilisateur;
use App\Models\PromotionUtilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PromoMail;
use Carbon\Carbon;

class PromotionController extends Controller
{
    /**
     * Retourne toutes les promotions avec un statut automatique
     */
    public function index()
    {
        $now = now();

        $promos = Promotion::all()->map(function ($p) use ($now) {
            $p->autoStatut = $this->computeStatus($p, $now);
            return $p;
        });

        return response()->json($promos, 200);
    }

    /**
     * Déterminer automatiquement le statut d’une promotion
     */
    private function computeStatus($promotion, $now)
    {
        if ($now->lt($promotion->dateDebut)) {
            return "en_attente";
        }
        if ($now->gt($promotion->dateFin)) {
            return "expiree";
        }
        return "active";
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomPromotion' => 'required|string|max:100',
            'typePromotion' => 'required|string|in:Pourcentage,Montant fixe',
            'codePromo' => 'required|string|max:50|unique:promotions,codePromo',
            'valeur' => 'required|numeric|min:0',
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after_or_equal:dateDebut',
            'montantMinimum' => 'nullable|numeric|min:0',
        ]);

        $promotion = Promotion::create($request->all());

        // Ajout du statut automatique
        $promotion->autoStatut = $this->computeStatus($promotion, now());

        return response()->json($promotion, 201);
    }

    public function show(string $id)
    {
        $promotion = Promotion::findOrFail($id);

        $promotion->autoStatut = $this->computeStatus($promotion, now());

        return response()->json($promotion, 200);
    }

    public function update(Request $request, string $id)
    {
        $promotion = Promotion::findOrFail($id);

        $request->validate([
            'nomPromotion' => 'sometimes|string|max:100',
            'typePromotion' => 'sometimes|string|in:Pourcentage,Montant fixe',
            'codePromo' => 'nullable|string|max:50|unique:promotions,codePromo,' . $id . ',numPromotion',
            'valeur' => 'sometimes|numeric|min:0',
            'dateDebut' => 'sometimes|date',
            'dateFin' => 'sometimes|date|after_or_equal:dateDebut',
            'montantMinimum' => 'nullable|numeric|min:0',
        ]);

        $promotion->update($request->all());

        $promotion->autoStatut = $this->computeStatus($promotion, now());

        return response()->json($promotion, 200);
    }

    public function destroy(string $id)
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->delete();

        return response()->json(null, 204);
    }


    /**
     * ENVOI DU CODE PROMO À UN CLIENT
     * + Empêche l’envoi si la promo est expirée ou pas commencée
     */
public function sendPromoToClient(Request $request)
{
    $request->validate([
        'numUtilisateur' => 'required|exists:utilisateurs,numUtilisateur',
        'numPromotion'   => 'required|exists:promotions,numPromotion',
        'nomClient'      => 'required|string',
       
    ]);

    $promotion = Promotion::findOrFail($request->numPromotion);
    $user      = Utilisateur::findOrFail($request->numUtilisateur);
    $now       = now();

    // Vérification du statut
    if ($now->lt($promotion->dateDebut)) {
        return response()->json(['success' => false, 'message' => "La promotion n'a pas encore commencé"], 400);
    }
    if ($now->gt($promotion->dateFin)) {
        return response()->json(['success' => false, 'message' => "La promotion est expirée"], 400);
    }

    // Déjà envoyé ?
    $dejaEnvoye = PromotionUtilisateur::where('numPromotion', $promotion->numPromotion)
        ->where('numUtilisateur', $user->numUtilisateur)
        ->exists();

    if ($dejaEnvoye) {
        return response()->json(['success' => false, 'message' => 'Promotion déjà envoyée à ce client'], 409);
    }

    // Enregistrement de l'envoi
    PromotionUtilisateur::create([
        'numPromotion'    => $promotion->numPromotion,
        'numUtilisateur'  => $user->numUtilisateur,
        'code_envoye'     => $promotion->codePromo,
        'date_expiration' => $promotion->dateFin,
        'statut'          => 'valide',
    ]);

    try {
        Mail::to($user->email)->send(new PromoMail([
            'nomClient'     => $request->nomClient,
            'codePromo'     => $promotion->codePromo,
            'valeur'        => $promotion->valeur,
            'type'          => $promotion->typePromotion,
            'dateFin'       => $promotion->dateFin,
            'nomPromotion'  => $promotion->nomPromotion,
        ]));
    } catch (\Exception $e) {
        \Log::error('Échec envoi mail promo', ['error' => $e->getMessage()]);
        // On ne bloque pas l'envoi même si le mail échoue
    }

    return response()->json([
        'success' => true,
        'message' => 'Code promo envoyé avec succès !'
    ]);
}


    /**
     * Vérifier si une promotion a déjà été envoyée à un utilisateur
     */
    public function checkIfSent($numPromotion, $numUtilisateur)
    {
        $existe = PromotionUtilisateur::where('numPromotion', $numPromotion)
            ->where('numUtilisateur', $numUtilisateur)
            ->exists();

        return response()->json(['sent' => $existe]);
    }


    /**
     * Valider un code promo
     */
    public function valider(Request $request)
    {
        $request->validate([
            'codePromo' => 'required|string',
            'numUtilisateur' => 'required|integer',
        ]);

        $promotion = Promotion::where('codePromo', $request->codePromo)->first();

        if (!$promotion) {
            return response()->json(['message' => 'Code promo invalide'], 400);
        }

        // Vérifier statut automatique
        $statut = $this->computeStatus($promotion, now());

        if ($statut === "expiree") {
            return response()->json(['message' => 'Code promo expiré'], 400);
        }

        $promoUser = PromotionUtilisateur::where('numPromotion', $promotion->numPromotion)
            ->where('numUtilisateur', $request->numUtilisateur)
            ->first();

        if (!$promoUser) {
            return response()->json(['message' => 'Code promo non attribué à cet utilisateur'], 400);
        }

        if ($promoUser->statut !== 'valide') {
            return response()->json(['message' => 'Code promo déjà utilisé ou invalide'], 400);
        }

        return response()->json([
            'message' => 'Code promo valide',
            'valeur' => $promotion->valeur,
            'typePromotion' => $promotion->typePromotion
        ], 200);
    }

    public function restore($id)
    {
        $promotion = Promotion::withTrashed()->find($id);

        if (!$promotion) return response()->json(['message' => 'Promotion non trouvée'], 404);

        $promotion->restore();

        return response()->json(['message' => 'Promotion restaurée', 'data' => $promotion]);
    }
}
