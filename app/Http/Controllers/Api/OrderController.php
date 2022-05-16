<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\OrderCollection;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin')->only(['index' , 'changeStatus']);

    }
    public function index()
    {
        $orders = Order::paginate(10);
        return new OrderCollection($orders);
    }
    public function user(User $user)
    {
        return new OrderCollection($user);
    }
    public function changeStatus(Request $request , Order $order)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|int|between:-2,101',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $order->payment()->update([
            'status' => $request->input('status'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
}

