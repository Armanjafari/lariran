<?php

namespace App\Support\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Notifications\Providers\OrderAdminProvider;
use App\Services\Notifications\Providers\OrderSuccessProvider;
use App\Support\Basket\Basket;
use App\Support\Cost\Contracts\CostInterface;
use App\Support\Payment\Gateways\Mellat;
use App\Support\Payment\Gateways\Sepehr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            // dd($e->getMessage() . 'erorr');
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
            'mellat' => Mellat::class,
            'sepehr' => Sepehr::class,
        ][$this->request->gateway];
        return resolve($gateway);
    }
    public function verify()
    {

        // TODO basket is not dynamic !
        $result = $this->gatewayFactory()->verify($this->request);
        if ((int)$result['status'] != 0) {
            $order = Order::where('code', $result['sale'])->firstOrFail();
            if ($order->payment->status == 1 || $order->payment->status == 2 || $order->payment->status == 3 || $order->payment->status == 10) {
                return false;
            }
            $order->payment()->update([
                'status' => 0,
            ]);
            return false;
        }
        $this->confirmPayment($result);
        $this->normalizeQuantity($result['order']);
        try {
            $notif = new OrderAdminProvider($result['order']->payment->amount, '+989177375015', $result['order']->id);
            $notif->send();
            $notif2 = new OrderSuccessProvider($result['order']->user->phone_number, $result['order']->id, $result['order']->user->name);
            $notif2->send();
            $notif3 = new OrderAdminProvider($result['order']->payment->amount, '+989176507221', $result['order']->id);
            $notif3->send();
        } catch (\Throwable $th) {
        }

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
        if ($result['gateway'] == 'mellat') {
            $result['order']->payment->result()->create($this->request->all());
        }else if ($result['gateway'] == 'sepehr'){
            $result['order']->payment->result()->create([
                'RefID' => $this->request->input('tracenumber'),
                'SaleOrderId' => $this->request->input('invoiceid'),
                'SaleReferenceId' => $this->request->input('rrn'),
                'CardHolderInfo' => $this->request->input('issuerbank'),
                'CardHolderPan' => $this->request->input('cardnumber'),
                'FinalAmount' => $this->request->input('amount'),
                'ResCode' => 0,

            ]);
        }
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
    //     $sms = new MeliPayamak($order->user , '' , 'مشتری گرامی خرید شما با موفقیت انجام شد');
    //     $sms->send();
    //     foreach ($order->products as $product) {
    //         $sms = new MeliPayamak($product->pivot->market->user , '' ,
    //          $product->product->pure->persian_title .
    //          '  ثبت شد محصول :' . $order->id .
    //           'فروشنده محترم سفارش شما با شماره');
    //         $sms->send();
    //     }
    // }

}
