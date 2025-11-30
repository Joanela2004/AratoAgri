<?php

namespace App\Http\Controllers\Api;

use App\Jobs\SendPromoEmailJob;
use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\PromotionUtilisateur;
use Illuminate\Http\Request;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Mail;
use App\Mail\PromoMail;
class PromotionController extends Controller
{
    public function index()
    {
        return response()->json(Promotion::all(), 200);
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
            'statutPromotion' => 'required|string|max:50',
            'montantMinimum' => 'nullable|numeric|min:0',

        ]);

        $promotion = Promotion::create($request->all());
        return response()->json($promotion, 201);
    }

    public function show(string $id)
    {
        $promotion = Promotion::findOrFail($id);
        return response()->json($promotion, 200);
    }

    public function update(Request $request, string $id)
    {
        $promotion = Promotion::findOrFail($id);

        $request->validate([
            'nomPromotion' => 'sometimes|string|max:100',
            'typePromotion' => 'sometimes|string|in:Pourcentage,Montant fixe',
            'codePromo' => 'nullable|string|max:50|unique:promotions,codePromo,'.$id.',numPromotion',
            'valeur' => 'sometimes|numeric|min:0',
            'dateDebut' => 'sometimes|date',
            'dateFin' => 'sometimes|date|after_or_equal:dateDebut',
            'statutPromotion' => 'sometimes|string|max:50',
            'montantMinimum' => 'nullable|numeric|min:0',

        ]);

        $promotion->update($request->all());
        return response()->json($promotion, 200);
    }

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
        'codePromo'      => 'required|exists:promotions,codePromo',
        'email'          => 'required|email',
        'nomClient' => 'required|string',
    ]);

    $promotion = Promotion::where('codePromo', $request->codePromo)->firstOrFail();
    $userId    = $request->numUtilisateur;

    // Éviter les doublons
    $dejaEnvoye = PromotionUtilisateur::where('numPromotion', $promotion->numPromotion)
                                       ->where('numUtilisateur', $userId)
                                       ->exists();

    if ($dejaEnvoye) {
        return response()->json(['message' => 'Déjà envoyé à ce client'], 409);
    }

  PromotionUtilisateur::create([
    'numPromotion'    => $promotion->numPromotion,
    'numUtilisateur'   => $userId,
    'code_envoye'      => $promotion->codePromo,   
    'date_expiration'  => $promotion->dateFin,
    'statut'           => 'valide',
]);

  Mail::to($request->email)->send(new PromoMail([
    'nomClient' => $request->nomClient,
    'codePromo' => $promotion->codePromo,
    'valeur'    => $promotion->valeur,
    'type'      => $promotion->typePromotion,
    'dateFin'   => $promotion->dateFin,
    'nomPromotion' => $promotion->nomPromotion,
]));

    return response()->json(['success' => true, 'message' => 'Code envoyé !']);
}
public function checkIfSent($numPromotion, $numUtilisateur)
{
    $existe = \DB::table('promotion_utilisateur')
        ->where('numPromotion', $numPromotion)
        ->where('numUtilisateur', $numUtilisateur)
        ->exists();

    return response()->json(['sent' => $existe]);
}

public function valider(Request $request)
{
    $request->validate([
        'codePromo' => 'required|string',
        'numUtilisateur' => 'required|integer',
    ]);

    $userId = $request->numUtilisateur;
    $code = $request->codePromo;

    // Chercher la promo
    $promotion = Promotion::where('codePromo', $code)->first();
    if (!$promotion) {
        return response()->json(['message' => 'Code promo invalide'], 400);
    }

    // Vérifier que l'utilisateur a bien reçu ce code
    $promoUser = PromotionUtilisateur::where('numPromotion', $promotion->numPromotion)
                                      ->where('numUtilisateur', $userId)
                                      ->first();

    if (!$promoUser) {
        return response()->json(['message' => 'Code promo non attribué à cet utilisateur'], 400);
    }

    // Vérifier expiration
    if ($promoUser->date_expiration && now()->greaterThan($promoUser->date_expiration)) {
        return response()->json(['message' => 'Code promo expiré'], 400);
    }

    // Vérifier statut
    if ($promoUser->statut !== 'valide') {
        return response()->json(['message' => 'Code promo déjà utilisé ou invalide'], 400);
    }

    return response()->json([
        'message' => 'Code promo valide',
        'valeur' => $promotion->valeur,
        'typePromotion' => $promotion->typePromotion
    ], 200);
}

};
