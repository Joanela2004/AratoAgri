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

public function sendEmailToUsers()
{
    $today = now();
    $promotions = Promotion::where('dateFin', '>=', $today)
                            ->where('statutPromotion', 'Active')
                            ->get();

    $users = Utilisateur::where('role', 'client')->get();

    foreach ($users as $user) {
        Mail::to($user->email)->send(new PromoMail($promotions));
    }

    return response()->json(['message' => 'E-mails envoyés avec succès'], 200);



}
public function validerCodePromo(Request $request)
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
    if ($promoUser->dateExpiration && now()->greaterThan($promoUser->dateExpiration)) {
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