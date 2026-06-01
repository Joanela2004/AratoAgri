<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Osen\Airtel\Collection; // SDK officiel

class AirtelController extends Controller
{
    public function createPayment(Request $request)
    {
        $montant = $request->input('montant');
        $numCommande = $request->input('numCommande');
        $telephoneClient = $request->input('telephone');

        $airtel = new Collection([
            'env' => env('APP_ENV') === 'production' ? 'live' : 'sandbox',
            'client_id' => env('AIRTEL_CLIENT_ID'),
            'client_secret' => env('AIRTEL_CLIENT_SECRET'),
            'public_key' => env('AIRTEL_PUBLIC_KEY'),
            'country' => 'MG', // Madagascar
            'currency' => 'MGA',
        ]);

        $response = $airtel->collect([
            'country' => 'MG',
            'msisdn' => $telephoneClient,
            'amount' => $montant,
            'currency' => 'MGA',
            'external_reference' => 'CMD-' . $numCommande,
            'payer_message' => 'Paiement commande #' . $numCommande,
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data['status'] === 'SUCCESSFUL') {
            return response()->json([
                'url' => $data['checkout_data']['checkout_url'], // Lien d'interface Airtel
                'transaction_id' => $data['data']['transaction_id'],
            ]);
        }

        return response()->json(['error' => $data['status_description']], 400);
    }

    // Webhook pour confirmation
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Signature');

        // Vérifie signature (avec ta clé Airtel)
        if (!hash_equals($signature, hash_hmac('sha256', json_encode($payload), env('AIRTEL_SECRET')))) {
            return response('Invalid signature', 400);
        }

        if ($payload['status'] === 'SUCCESSFUL') {
            $numCommande = str_replace('CMD-', '', $payload['external_reference']);
            Commande::where('referenceCommande', $numCommande)->update(['statut' => 'payée']);
        }

        return response('OK', 200);
    }
}