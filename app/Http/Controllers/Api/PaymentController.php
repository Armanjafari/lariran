<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Payment\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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
        ? $this->sendSuccessResponse()
        : $this->sendErrorResponse();
    }
    private function sendErrorResponse()
    {
        $this->sessionInvladiate();
        return redirect('https://lariran.com/payment/callback/transaction/failed');
    }
    private function sendSuccessResponse()
    {
        $this->sessionInvladiate();
        return redirect('https://lariran.com/payment/callback/transaction/success');

    }
    private function sessionInvladiate()
    {
        Session::flush();
        Auth::logout();
    }
}
