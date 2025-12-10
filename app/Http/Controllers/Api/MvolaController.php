<?php

namespace App\Http\Controllers\Api;

use App\Services\MvolaService;
use Illuminate\Http\Request;

class MvolaController extends Controller
{
    protected $mvola;

    public function __construct(MvolaService $mvola)
    {
        $this->mvola = $mvola;
    }

    public function generateToken()
    {
        try {
            return response()->json($this->mvola->generateToken());
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function pay(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric',
                'customerNumber' => 'required',
            ]);

            $token = str_replace("Bearer ", "", $request->header("Authorization"));

            $result = $this->mvola->merchantPay(
                $token,
                $request->amount,
                $request->customerNumber
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
}
