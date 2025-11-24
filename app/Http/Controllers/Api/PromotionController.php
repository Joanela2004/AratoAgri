<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

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

    $users = User::all();

    foreach ($users as $user) {
        Mail::to($user->email)->send(new PromoMail($promotions));
    }

    return response()->json(['message' => 'E-mails envoyés avec succès'], 200);
}


}