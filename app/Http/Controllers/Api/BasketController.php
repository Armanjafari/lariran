<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Exceptions\QuantityExceededException;
use App\Http\Resources\v1\BasketCollection;
use App\Models\Full;
use App\Models\Shiping;
use App\Models\User;
use App\Services\Convert\convertEnglishToPersian;
use App\Support\Basket\Basket;
use App\Support\Cost\Contracts\CostInterface;
use App\Support\Payment\Transaction;
use App\Support\Storage\Contracts\StorageInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Exists;

class BasketController extends Controller
{
    private $basket;
    private $transaction;
    public function __construct(Basket $basket, Transaction $transaction)
    {
        $this->middleware('auth:sanctum')->only(['checkCost']);
        $this->middleware('throttle:5,5')->only(['checkout']);
        $this->basket = $basket;
        $this->transaction = $transaction;
    }
    public function index(CostInterface $cost)
    {
        $items = $this->basket->all();
        return new BasketCollection($items);
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
        }
        // dd($cookie);
        $cookie = Cookie::get('laravel_session');
        try {
            $this->basket->add($product, 1);
            return response()->json([
                'data' => [
                    'basket' => 'محصول به سبد خرید اضافه شد',
                    'cookie' => $cookie,
                ],
                'status' => 'success',
            ]);
            // return back()->with('success', "");
        } catch (QuantityExceededException $e) {
            return response()->json([
                'data' => [
                    'basket' => 'محصول موجود نمیباشد',
                    'cookie' => $cookie,
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
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $cookie = Cookie::get('laravel_session');
        try {
            $this->basket->update($product, $request->quantity);
            return response()->json([
                'data' => [
                    'basket' => ' سبد خرید ویرایش شد ',
                    'cookie' => $cookie,
                ],
                'status' => 'success',
            ]);
        } catch (QuantityExceededException $e) {
            return response()->json([
                'data' => [
                    'basket' => 'مقدار مورد نظر بیش از موجودی انبار میباشد',
                    'cookie' => $cookie,
                ],
                'status' => 'error',
            ]);
        }
    }
    // public function checkoutForm()
    // {
    //     return view('Product.checkout');
    // }
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required',
            'user_id' => 'required|integer|exists:users,id',
            'shipping' => 'required|exists:shipings,id',
            'gateway' => 'required_if:method,online'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        if (!$this->basket->itemCount()) {
            return response()->json([
                'data' => ['basket' => 'سبد خرید خالی است'],
                'status' => 'error',
            ]);
        }
        $user = User::find($request->user_id);
        Auth::login($user);
        if (!$user->id == Shiping::find($request->shipping)->user_id) {
            return response()->json([
                'data' => [
                    'shipping' => 'آدرس وارد شده صحیح نمیباشد'
                ],
                'status' => 'error',
            ]);
        }


        // $this->validateForm($request);
        $order = $this->transaction->checkout(); // TODO check this
        dd($order);
        return response()->json([
            'data' => [
                'order' => 'سفارش شما با شماره' . $order->id ?? ''
            ],
            'status' => 'success
            '
        ]);
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
    public function checkCost(CostInterface $cost, Request $request)
    { // TODO end this
        $validator = Validator::make($request->all(), [
            'shipping' => 'required|exists:shipings,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        try {
            if (!auth()->id() == Shiping::find($request->shipping)->user_id) {
                return response()->json([
                    'data' => [
                        'shipping' => 'آدرس وارد شده صحیح نمیباشد'
                    ],
                    'status' => 'error',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'data' => 'unAuthrozied',
                'status' => 'error',
            ]);
        }

        $full_basket = $cost->getSummary();
        $full_basket['مجموع'] = convertEnglishToPersian::convertEnglishToPersian($cost->getTotalCosts());
        return response()->json([
            'data' => $full_basket,
            'status' => 'success',
        ]);
        // dd($cost->getTotalCosts());
    }
}
