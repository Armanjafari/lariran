<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\OrderCollection;
use App\Models\Order;
use App\Models\User;
use App\Services\Notifications\Providers\OrderPostalProvider;
use App\Services\Notifications\Providers\OrderStatusProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin')->only(['index', 'changeStatus','changeTrackingCode']);
    }
    public function index()
    {
        $orders = Order::orderBy('created_at','desc')->paginate(10);
        $orders->load('fulls.product.images');
        return new OrderCollection($orders);
    }
    public function user(User $user)
    {
        $orders = $user->orders()->orderBy('created_at','desc')->get();
        return new OrderCollection($orders);
    }
    public function changeStatus(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|int',
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
        $notif = new OrderStatusProvider($order->user->phone_number, __('orders.' . $request->input('status')));
        $notif->send();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function changeTrackingCode(Order $order, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tracking_code' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $order->payment()->update([
            'trackingCode' => $request->input('tracking_code'),
        ]);
        $notif = new OrderPostalProvider($order->user->phone_number,$request->input('tracking_code'));
        $notif->send();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
}
