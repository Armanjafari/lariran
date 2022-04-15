<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Exceptions\QuantityExceededException;
use App\Models\Full;
use App\Models\Product;
use App\Support\Basket\Basket;
use App\Support\Payment\Transaction;
use App\Support\Storage\Contracts\StorageInterface;
use Illuminate\Http\Request;

class BasketController extends Controller
{
    private $basket;
    private $transaction;
    public function __construct(Basket $basket, Transaction $transaction)
    {
        $this->middleware('auth:sanctum')->only(['checkoutForm' , 'checkout']);
        $this->basket = $basket;
        $this->transaction = $transaction;
        
    }
    public function index()
    {
        $items = $this->basket->all();
        //dd($items);
        // return view('Product.basket',compact('items'));
        return response()->json([
            'data' => $items,
            'status' => 'success',
        ]);
    }
    public function add(Full $product)
    {
        if (!$product->is_active) {
            return response()->json([
                'data' => [
                    'basket' => 'محصول مورد نظر وجود ندارد'
                ],
                'status' => 'error',
            ]);
            // return back()->with('error', "محصول مورد نظر وجود ندارد");

        }
        try {
            $this->basket->add($product , 1);
            return response()->json([
                'data' => [
                    'basket' => 'محصول به سبد خرید اضافه شد'
                ],
                'status' => 'success',
            ]);
            // return back()->with('success', "");
        } catch (QuantityExceededException $e) {
            return response()->json([
                'data' => [
                    'basket' => 'محصول موجود نمیباشد'
                ],
                'status' => 'error',
            ]);
            // return back()->with('error', '');
        }
        
    }

    public function clear()
    {
        resolve(StorageInterface::class)->clear();
    }
    public function update(Request $request , Full $product)
    {
        $this->basket->update($product,$request->quantity);
        return response()->json([
            'data' => [
                'basket' => ' سبر خرید ویرایش شد '
            ],
            'status' => 'success',
        ]);
    }
    public function checkoutForm()
    {
        return view('Product.checkout');
    }
    public function checkout(Request $request)
    {
        $this->validateForm($request);
        $order = $this->transaction->checkout(); // TODO check this
        return redirect()->route('index')->withSuccess('سفارش شما با شماره' . $order->id ?? '');
    }
    private function validateForm(Request $request)
    {
        $request->validate([
            'method' => ['required'],
            'gateway' => ['required_if:method,online']
        ]);
    }
}
