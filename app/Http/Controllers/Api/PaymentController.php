<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Payment\Transaction;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private $transaction;
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
    public function verify(Request $request)
    {
        return $this->transaction->verify()
        ? $this->sendSuccessResponse($request)
        : $this->sendErrorResponse($request);
    }
    private function sendErrorResponse(Request $request)
    {
        dd($request->all());
        return response()->json([
            'data' => 'تراکنش با خطا مواجه شد',
            'status' => 'error',
        ]);
    }
    private function sendSuccessResponse(Request $request)
    {
        dd($request->all());
        return response()->json([
            'data' => 'تراکنش با موفقیت انجام شد',
            'status' => 'success',
        ]);
    }
}
