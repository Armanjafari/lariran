<?php

namespace App\Support\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Notifications\Providers\OrderAdminProvider;
use App\Services\Notifications\Providers\OrderUserProvider;
use App\Support\Basket\Basket;
use App\Support\Cost\Contracts\CostInterface;
use App\Support\Payment\Gateways\Mellat;
use App\Support\Payment\Gateways\Saman;
use App\Support\Payment\Gateways\Pasargad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Transaction
{
    private $request;
    private $basket;
    private $cost;
    public function __construct(Request $request, Basket $basket, CostInterface $cost)
    {
        $this->request = $request;
        $this->basket = $basket;
        $this->cost = $cost;
    }
    public function checkout()
    {
        DB::beginTransaction();
        try {
            $order = $this->makeOrder();
            $payment = $this->makePayment($order);
            DB::commit();
            // dd('payment was successful');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage() . 'erorr');
            return null;
        }
        if ($payment->isOnline()) {
            return $this->gatewayFactory()->pay($order, $this->cost->getTotalCosts());
        } // chech this normalize
        $this->normalizeQuantity($order);
        // $this->normalizeWallet($order);
        // $this->sendSms($order);
        $this->basket->clear();
        return $order;
    }
    private function gatewayFactory()
    {
        $gateway = [
            'saman' => Saman::class,
            'mellat' => Mellat::class,
            'pasargad' => Pasargad::class
        ][$this->request->gateway];
        return resolve($gateway);
    }
    public function verify()
    {
        // TODO basket is not dynamic !
        $result = $this->gatewayFactory()->verify($this->request);
        if ($result['status'] != 0) {
            $order = Order::where('code', $result['sale'])->firstOrFail();
            $order->payment()->update([
                'status' => 0,
            ]);
            return false;
        }
        $this->confirmPayment($result);
        $this->normalizeQuantity($result['order']);
        $notif = new OrderAdminProvider($result['order']->payment->amount, '+989177375015');
        $notif->send();
        $notif2 = new OrderUserProvider($result['order']->user->phone_number, $result['order']->id);
        $notif2->send();
        $notif3 = new OrderAdminProvider($result['order']->payment->amount, '+989176507221');
        $notif3->send();
        // $this->normalizeWallet($result['order']);
        // $this->sendSms($result['order']);
        $this->basket->clear();
        return true;
    }

    private function normalizeQuantity($order)
    {
        foreach ($order->fulls as $product) {
            $product->decrementStock($product->pivot->quantity);
        }
    }
    private function confirmPayment($result)
    {
        $result['order']->payment->result()->create($this->request->all());
        return $result['order']->payment->confirm($result['refNum'], $result['gateway']);
    }
    private function makeOrder()
    {
        $order = Order::create([
            'user_id' => auth()->user()->id,
            'code' => time(),
            'amount' => $this->basket->subTotal(),
            'shiping_id' => $this->request->input('shipping'),
        ]);
        // dd($this->products());
        $order->fulls()->attach($this->products());
        return $order;
    }
    private function makePayment($order)
    {

        return Payment::create([
            'order_id' => $order->id,
            'method' => $this->request->method,
            'amount' => $this->cost->getTotalCosts(),
            'status' => 100
        ]);
    }
    private function products()
    {
        $products = [];
        foreach ($this->basket->all() as $product) {
            $products[$product->id] = [
                'quantity' => $product->quantity,
                'price' => ($product->price * $product->currency->value),
            ];
        }
        return $products;
    }
    // private function normalizeWallet($order)
    // {
    //     // TODO refactor needed
    //     foreach ($order->products as $product) {
    //         $profit = ($product->pivot->category_id / 100) * ($product->pivot->price * $product->pivot->quantity);
    //         $final = ($product->pivot->price * $product->pivot->quantity)  - $profit;
    //         $product->product->market->increaseWallet($final);
    //         $product->product->market->increaseProfit($profit);
    //     }
    // }
    // private function sendSms($order)
    // {
    //     $sms = new MeliPayamak($order->user , '' , '?????????? ?????????? ???????? ?????? ???? ???????????? ?????????? ????');
    //     $sms->send();
    //     foreach ($order->products as $product) {
    //         $sms = new MeliPayamak($product->pivot->market->user , '' ,
    //          $product->product->pure->persian_title .
    //          '  ?????? ???? ?????????? :' . $order->id .
    //           '?????????????? ?????????? ?????????? ?????? ???? ??????????');
    //         $sms->send();
    //     }
    // }

}
