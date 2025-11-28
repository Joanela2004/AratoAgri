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
        $panier = $request->input('panier', []);
        $fraisLivraison = $request->input('fraisLivraison', 0);
        $remise = $request->input('remise', 0);
        $numCommande = $request->input('numCommande');
        $userId = $request->input('user_id');

        Stripe::setApiKey(env('STRIPE_SECRET')); // Tes clés test marchent

        try {
            // 1. Calcul total en Ariary
            $totalProduits = 0;
            foreach ($panier as $item) {
                $prixUnitaire = $item['prixApresDecoupe'] ?? $item['prixPerKg'] ?? 0;
                $poids = $item['poids'] ?? 1;
                $totalProduits += $prixUnitaire * $poids;
            }

            $montantTotalAr = $totalProduits + $fraisLivraison - $remise;

            // 2. Conversion en USD (1 USD ≈ 4500 Ar – taux moyen 2025)
            $tauxUsd = 4500;
            $montantUsd = $montantTotalAr / $tauxUsd;

            // 3. Création session Stripe en USD
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd', // ← USD (fonctionne à 100 % sur ton compte)
                        'product_data' => [
                            'name' => 'Commande #' . $numCommande . ' – ' . number_format($montantTotalAr, 0, ',', ' ') . ' Ar',
                        ],
                        'unit_amount' => intval($montantUsd * 100), // centimes
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => env('FRONTEND_URL') . '/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => env('FRONTEND_URL') . '/cancel',
                'metadata' => [
                    'numCommande' => $numCommande,
                    'user_id' => $userId,
                    'montant_ariary' => $montantTotalAr,
                ],
            ]);

            return response()->json([
                'url' => $session->url,
                'session_id' => $session->id,
                'montant_ariary' => $montantTotalAr,
                'montant_usd' => round($montantUsd, 2),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Webhook (inchangé – parfait)
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $numCommande = $session->metadata->numCommande;

            $commande = Commande::where('numCommande', $numCommande)->first();
            if ($commande) {
                $commande->update(['statut' => 'payée']);
                // Optionnel : envoyer email, notifier admin, etc.
            }
        }

        return response()->json(['status' => 'success']);
    }
}