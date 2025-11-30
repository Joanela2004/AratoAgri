<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Produit;
use Illuminate\Support\Facades\DB;

class StripeController extends Controller
{

public function createCheckoutSession(Request $request)
{
    $request->validate([
        'referenceCommande' => 'required|string',
        'montantTotal'      => 'required|numeric|min:1',
        'numModePaiement'   => 'required|integer|exists:mode_Paiements,numModePaiement',
    ]);

    $referenceCommande = $request->referenceCommande;
    $montantTotalAr    = $request->montantTotal;
    $numModePaiement   = $request->numModePaiement;

    Stripe::setApiKey(env('STRIPE_SECRET'));

    $session = Session::create([
        'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'mga',
            'product_data' => [
                'name' => "Commande #{$referenceCommande}",
            ],
            'unit_amount' => $montantTotalAr * 100, 
        ],
        'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => env('FRONTEND_URL') . '/success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => env('FRONTEND_URL') . '/cancel',
        'metadata' => [
            'referenceCommande' => $referenceCommande,
            'montant_ariary'    => $montantTotalAr,
            'numModePaiement'   => $numModePaiement, // BIEN ENVOYÉ
        ],
    ]);

    return response()->json([
        'url' => $session->url,
        'session_id' => $session->id,
    ]);
}

public function webhook(Request $request)
{
    $payload = $request->getContent();
    $sigHeader = $request->header('Stripe-Signature');
    $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

       if (!$endpointSecret) {
        \Log::warning('STRIPE_WEBHOOK_SECRET manquant dans .env');
    }

    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
    } catch (\Exception $e) {
        \Log::error('Webhook signature verification failed.', ['error' => $e->getMessage()]);
        return response('Unauthorized', 400);
    }

    // PAIEMENT RÉUSSI
    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;

        $referenceCommande = $session->metadata->referenceCommande ?? null;
        $numModePaiement = $session->metadata->numModePaiement ?? null;

        if (!$referenceCommande || !$numModePaiement) {
            \Log::error('Metadata manquantes dans webhook Stripe', (array)$session->metadata);
            return response()->json(['status' => 'missing metadata'], 400);
        }

        $commande = Commande::where('referenceCommande', $referenceCommande)->first();

        if (!$commande) {
            \Log::error('Commande non trouvée', ['ref' => $referenceCommande]);
            return response()->json(['status' => 'commande not found'], 404);
        }

        if ($commande->statut === 'payée') {
            return response()->json(['status' => 'already paid'], 200);
        }

        DB::transaction(function () use ($commande, $numModePaiement) {
            // 1. Mettre à jour la commande
            $commande->update([
                'statut' => 'payée',
                'numModePaiement' => $numModePaiement,
            ]);

            // 2. Créer ou mettre à jour le paiement
            Paiement::updateOrCreate(
                ['numCommande' => $commande->numCommande],
                [
                    'numModePaiement' => $numModePaiement,
                    'montantApayer'   => $commande->montantTotal,
                    'statut'          => 'effectué',
                    'datePaiement'    => now(),
                ]
            );

            // 3. Décrémenter le stock
            foreach ($commande->detailCommandes as $detail) {
                Produit::where('numProduit', $detail->numProduit)
                    ->decrement('poids', $detail->poids);
            }

            \Log::info('COMMANDE PAYÉE VIA STRIPE', [
                'reference' => $commande->referenceCommande,
                'mode_paiement' => $numModePaiement,
                'montant' => $commande->montantTotal
            ]);
        });
    }

    return response()->json(['status' => 'success']);
}

}