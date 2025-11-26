<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        $cart = $request->input('panier', []);
        $fraisLivraisonTotal = $request->input('fraisLivraison', 0);
        $remise = $request->input('remise', 0);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $totalProduits = 0;

            // Calcul du total panier
            foreach ($cart as $item) {
                $prixUnitaire = $item['prixApresDecoupe'] ?? $item['prixPerKg'] ?? 0;
                $poids = $item['poids'] ?? 1;
                $totalProduit = $prixUnitaire * $poids;
                $totalProduits += $totalProduit;
            }

            // Montant final = total produits + frais livraison - remise
            $montantTotal = $totalProduits + $fraisLivraisonTotal - $remise;

            // Stripe attend le montant en centimes
            $lineItems = [
                [
                    'price_data' => [
                        'currency' => 'mga',
                        'product_data' => [
                            'name' => 'Commande totale',
                        ],
                        'unit_amount' => intval($montantTotal * 100),
                    ],
                    'quantity' => 1,
                ]
            ];

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => env('FRONTEND_URL') . '/success',
                'cancel_url' => env('FRONTEND_URL') . '/cancel',
            ]);

            return response()->json([
                'url' => $session->url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
