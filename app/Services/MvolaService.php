<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MvolaService
{
    public function generateToken()
    {
        $url = env('PRODUCT_URL') . '/token';

        $response = Http::asForm()->withHeaders([
            'Authorization' => 'Basic ' . base64_encode(env('CONSUMER_KEY') . ':' . env('CONSUMER_SECRET')),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->post($url, [
            'grant_type' => 'client_credentials',
            'scope' => 'EXT_INT_MVOLA_SCOPE'
        ]);

        return $response->json();
    }

    public function merchantPay($token, $amount, $customerNumber)
    {
        $url = env('PRODUCT_URL') . '/mvola/mm/transactions/type/merchantpay/1.0.0/';
        
        $body = [
            "amount" => $amount,
            "currency" => "Ar",
            "descriptionText" => "test Mvola",
            "requestingOrganisationTransactionReference" => "TX" . time(),
            "requestDate" => now()->toISOString(),
            "originalTransactionReference" => "MVOLA_PAYMENT" . time(),
            "debitParty" => [["key" => "msisdn", "value" => $customerNumber]],
            "creditParty" => [["key" => "msisdn", "value" => env('MERCHANT_NUMBER')]],
            "metadata" => [
                ["key" => "partnerName", "value" => env('MERCHANT_NUMBER')],
                ["key" => "fc", "value" => "USD"],
                ["key" => "amountFc", "value" => "1"],
            ]
        ];

        $headers = [
            "Version" => "1.0",
            "X-CorrelationID" => "TX-" . time(),
            "UserLanguage" => "MG",
            "UserAccountIdentifier" => "msisdn;" . env('MERCHANT_NUMBER'),
            "partnerName" => "The Kernel Mada",
            "Accept-Charset" => "UTF-8",
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json",
            "Cache-Control" => "no-cache",
        ];

        $response = Http::withHeaders($headers)->post($url, $body);

        return $response->json();
    }
}
