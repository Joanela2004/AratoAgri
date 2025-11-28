<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Commande;

class StripeController extends Controller
{

public function createCheckoutSession(Request $request)
{
    $request->validate([
        'referenceCommande' => 'required|string',
        'montantTotal' => 'required|numeric|min:1',
    ]);

    $referenceCommande = $request->input('referenceCommande');
    $montantTotalAr = $request->input('montantTotal'); 

    Stripe::setApiKey(env('STRIPE_SECRET'));

    try {
        $tauxUsd = 4500;
        $montantUsd = $montantTotalAr / $tauxUsd;

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => "Commande #{$referenceCommande} – " . number_format($montantTotalAr, 0, ',', ' ') . ' Ar',
                    ],
                    'unit_amount' => intval($montantUsd * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => env('FRONTEND_URL') . '/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => env('FRONTEND_URL') . '/cancel',
            'metadata' => [
                'referenceCommande' => $referenceCommande,
                'montant_ariary' => $montantTotalAr,
            ],
        ]);

        return response()->json([
            'url' => $session->url,
            'session_id' => $session->id,
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

  public function webhook(Request $request)
{
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');
    $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
    } catch (\Exception $e) {
        return response('Webhook error: ' . $e->getMessage(), 400);
    }

    // Paiement réussi
    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;

        $referenceCommande = $session->metadata->referenceCommande ?? null;
        if (!$referenceCommande) {
            return response()->json(['status' => 'no reference'], 200);
        }

        $commande = Commande::where('referenceCommande', $referenceCommande)->first();
        if (!$commande) {
            return response()->json(['status' => 'commande not found'], 404);
        }

        // Éviter double traitement
        if ($commande->statut === 'payée') {
            return response()->json(['status' => 'already paid'], 200);
        }

        DB::transaction(function () use ($commande, $session) {
            // 1. Mettre à jour le paiement
            $paiement = Paiement::where('numCommande', $commande->numCommande)->first();
            if ($paiement) {
                $paiement->update([
                    'statut' => 'effectué',
                    'datePaiement' => now(),
                ]);
            }

            // 2. Mettre à jour la commande
            $commande->update(['statut' => 'payée']);

            // 3. Déduire le stock (une seule fois !)
            foreach ($commande->detailCommandes as $detail) {
                Produit::where('numProduit', $detail->numProduit)
                    ->decrement('poids', $detail->poids);
            }
        });

      
    }

    return response()->json(['status' => 'success']);
}
}