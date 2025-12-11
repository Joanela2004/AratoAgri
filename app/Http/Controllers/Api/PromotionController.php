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
     public function index()
    {
        return response()->json(Promotion::all());
    }

    // Création d'une nouvelle promotion
    public function store(Request $request)
    {
        $request->validate([
            'nomPromotion'    => 'required|string|max:100',
            'typePromotion'   => 'required|in:Pourcentage,Montant fixe',
            'valeur'          => 'required|numeric|min:0.01',
            'automatique'     => 'required|boolean',
            'dateDebut'       => 'required|date',
            'dateFin'         => 'required|date|after_or_equal:dateDebut',
            'montantMinimum'  => 'nullable|numeric|min:0',
            'codePromo'       => 'nullable|string|max:50|unique:promotions,codePromo',
            'statutPromotion' => 'sometimes|boolean',
        ]);

        $data = $request->only([
            'nomPromotion', 'typePromotion', 'valeur', 'dateDebut', 'dateFin',
            'automatique', 'montantMinimum', 'codePromo', 'statutPromotion'
        ]);

        if ($data['automatique']) {
            $data['codePromo'] = null;
        } else {
       $data['codePromo'] = $request->filled('codePromo') ? strtoupper($request->codePromo) : null;
}

        $data['montantMinimum']   = $data['montantMinimum'] ?? 0;
        $data['statutPromotion']  = $data['statutPromotion'] ?? true;

        $promotion = Promotion::create($data);

        return response()->json($promotion, 201);
    }

    public function update(Request $request, string $id)
    {
        $promotion = Promotion::findOrFail($id);

        $request->validate([
            'nomPromotion'    => 'sometimes|string|max:100',
            'typePromotion'   => 'sometimes|in:Pourcentage,Montant fixe',
            'valeur'          => 'sometimes|numeric|min:0.01',
            'automatique'     => 'sometimes|boolean',
            'dateDebut'       => 'sometimes|date',
            'dateFin'         => 'sometimes|date|after_or_equal:dateDebut',
            'montantMinimum'  => 'nullable|numeric|min:0',
            'codePromo'       => 'nullable|string|max:50|unique:promotions,codePromo,' . $id . ',numPromotion',
            'statutPromotion' => 'sometimes|boolean',
        ]);

        $data = $request->only([
            'nomPromotion', 'typePromotion', 'valeur', 'dateDebut', 'dateFin',
            'automatique', 'montantMinimum', 'codePromo', 'statutPromotion'
        ]);

        if (isset($data['automatique'])) {
            if ($data['automatique']) {
                $data['codePromo'] = null;
            } else {
               $data['codePromo'] = isset($data['codePromo']) && $data['codePromo'] !== ''
            ? strtoupper($data['codePromo'])
            : null;
            }
        }

        $data['montantMinimum'] = $data['montantMinimum'] ?? $promotion->montantMinimum;
        $promotion->update($data);

        return response()->json($promotion);
    }

    // Validation d'un code promo (panier)
    public function valider(Request $request)
    {
        $request->validate([
            'codePromo'       => 'required|string',
            'numUtilisateur' => 'required|integer',
            'montantPanier'   => 'required|numeric|min:0',
        ]);

        $promotion = Promotion::where('codePromo', $request->codePromo)->first();

        if (!$promotion || $promotion->statut !== 'active') {
            return response()->json(['message' => 'Code promo invalide ou inactif'], 400);
        }

        $promoUser = PromotionUtilisateur::where('numPromotion', $promotion->numPromotion)
            ->where('numUtilisateur', $request->numUtilisateur)
            ->first();

        if (!$promoUser || $promoUser->statut !== 'valide') {
            return response()->json(['message' => 'Ce code promo ne vous a pas été attribué ou a déjà été utilisé'], 400);
        }

        if ($promotion->montantMinimum > $request->montantPanier) {
            return response()->json([
                'message'             => 'Montant minimum non atteint',
                'montantMinimumRequis'=> $promotion->montantMinimum,
                'manque'              => $promotion->montantMinimum - $request->montantPanier,
            ], 400);
        }

        $reduction = $promotion->typePromotion === 'Pourcentage'
            ? $request->montantPanier * ($promotion->valeur / 100)
            : $promotion->valeur;

        $reduction = round($reduction / 100) * 100;

        return response()->json([
            'message'       => 'Code promo valide',
            'valeur'        => $promotion->valeur,
            'typePromotion' => $promotion->typePromotion,
            'reductionEnAr' => $reduction,
            'montantAPayer' => $request->montantPanier - $reduction,
        ]);
    }

    // Application automatique d'une promotion
    public function appliquerAuto(Request $request)
    {
        $montantPanier = $request->input('montantPanier');
        $now = Carbon::now();

        $promo = Promotion::where('automatique', true)
            ->where('statutPromotion', true)
            ->where('dateDebut', '<=', $now)
            ->where('dateFin', '>=', $now)
            ->where('montantMinimum', '<=', $montantPanier)
            ->first();

        if (!$promo) {
            return response()->json(['message' => 'Aucune promotion automatique applicable'], 404);
        }

        $reduction = $promo->typePromotion === 'Pourcentage'
            ? $montantPanier * ($promo->valeur / 100)
            : $promo->valeur;

        $reduction = round($reduction / 100) * 100;

        return response()->json([
            'message'       => 'Promotion automatique appliquée',
            'reduction'     => $reduction,
            'montantAPayer' => $montantPanier - $reduction,
            'promotion'     => $promo
        ]);
    }

    // Suppression
    public function destroy(string $id)
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->delete();
        return response()->json(null, 204);
    }

    public function sendPromoToClient(Request $request)
    {
        $request->validate([
            'numUtilisateur' => 'required|exists:utilisateurs,numUtilisateur',
            'numPromotion'   => 'required|exists:promotions,numPromotion',
            'nomClient'      => 'required|string',
        ]);

        $promotion = Promotion::findOrFail($request->numPromotion);
        $user = Utilisateur::findOrFail($request->numUtilisateur);
        $now = Carbon::now();

        if ($promotion->automatique) {
            return response()->json(['success' => false, 'message' => "Impossible d'envoyer un code : cette promotion est automatique."], 400);
        }

        if (!$promotion->codePromo) {
            return response()->json(['success' => false, 'message' => "Aucun code promo n'est défini pour cette promotion."], 400);
        }

        if ($now->lt($promotion->dateDebut)) {
            return response()->json(['success' => false, 'message' => "La promotion n'a pas encore commencé"], 400);
        }

        if ($now->gt($promotion->dateFin)) {
            return response()->json(['success' => false, 'message' => "La promotion est expirée"], 400);
        }

        $dejaEnvoye = PromotionUtilisateur::where('numPromotion', $promotion->numPromotion)
            ->where('numUtilisateur', $user->numUtilisateur)
            ->exists();

        if ($dejaEnvoye) {
            return response()->json(['success' => false, 'message' => 'Promotion déjà envoyée à ce client'], 409);
        }

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
        }

        return response()->json(['success' => true, 'message' => 'Code promo envoyé avec succès !']);
    }

    public function checkIfSent($numPromotion, $numUtilisateur)
    {
        $existe = PromotionUtilisateur::where('numPromotion', $numPromotion)
            ->where('numUtilisateur', $numUtilisateur)
            ->exists();

        return response()->json(['sent' => $existe]);
    }

    public function restore($id)
    {
        $promotion = Promotion::withTrashed()->find($id);

        if (!$promotion) {
            return response()->json(['message' => 'Promotion non trouvée'], 404);
        }

        $promotion->restore();

        return response()->json(['message' => 'Promotion restaurée', 'data' => $promotion]);
    }
}