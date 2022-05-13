<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Exceptions\QuantityExceededException;
use App\Http\Resources\v1\BasketCollection;
use App\Models\Full;
use App\Models\Shiping;
use App\Support\Basket\Basket;
use App\Support\Payment\Transaction;
use App\Support\Storage\Contracts\StorageInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BasketController extends Controller
{
    private $basket;
    private $transaction;
    public function __construct(Basket $basket, Transaction $transaction)
    {
        $this->middleware('auth:sanctum')->only(['checkoutForm', 'checkout']);
        $this->basket = $basket;
        $this->transaction = $transaction;
    }
    public function index()
    {
        $items = $this->basket->all();
        return new BasketCollection($items);
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
                    'basket' => 'محصول مورد نظر بیش از ظرفیت انبار است'
                ],
                'status' => 'error',
            ]);
            // return back()->with('error', "محصول مورد نظر وجود ندارد");

        }
        try {
            $this->basket->add($product, 1);
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
    public function update(Request $request, Full $product)
    {
        try {
            $this->basket->update($product, $request->quantity);
            return response()->json([
                'data' => [
                    'basket' => ' سبر خرید ویرایش شد '
                ],
                'status' => 'success',
            ]);
        } catch (QuantityExceededException $e) {
            return response()->json([
                'data' => [
                    'basket' => 'مقدار مورد نظر بیش از موجودی انبار میباشد'
                ],
                'status' => 'error',
            ]);
        }
    }
    public function checkoutForm()
    {
        return view('Product.checkout');
    }
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required',
            'shipping' => 'required|exists:shipings,id',
            'gateway' => 'required_if:method,online'
        ]);
        if (!auth()->id() == Shiping::find($request->shipping)->id) {
            return response()->json([
                'data' => [
                    'shipping' => 'آدرس وارد شده صحیح نمیباشد'
                ],
                'status' => 'error',
            ]);
        }
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }



        // $this->validateForm($request);
        $order = $this->transaction->checkout(); // TODO check this
        dd($order);
        return redirect()->route('index')->withSuccess('سفارش شما با شماره' . $order->id ?? '');
    }
    private function validateForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required',
            'gateway' => 'required_if:method,online'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
    }
}
