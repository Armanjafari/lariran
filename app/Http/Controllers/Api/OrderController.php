<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\OrderAdminByStatusCollection;
use App\Http\Resources\v1\OrderCollection;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Notifications\Providers\OrderPostalProvider;
use App\Services\Notifications\Providers\OrderStoreConfirmationProvider;
use App\Services\Notifications\Providers\OrderSuccessProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin')->only(['index', 'changeStatus', 'changeTrackingCode']);
    }
    public function index(Request $request)
    { // TODO fix this bad code
        $validator = Validator::make($request->all(), [
            'status' => 'integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        if ($request->has('status')) {
            if (!is_null($request->input('status')) && !empty($request->input('status'))) {
                $payments = Payment::where('status', (int)$request->input('status'))->orderBy('created_at', 'desc')->paginate(10);
                if ($request->has('s')) {
                    if (!is_null($request->input('s')) && !empty($request->input('s'))) {
                        $query = $request->input('s');
                        $payments = Payment::where('status', (int)$request->input('status'))
                            ->whereRelation('order', 'id', 'LIKE', '%' . $query . '%')
                            ->orderBy('created_at', 'desc')->paginate(10);
                    }
                }
                return new OrderAdminByStatusCollection($payments);
            }
        }
        if ($request->has('s')) {
            if (!is_null($request->input('s')) && !empty($request->input('s'))) {
                $query = $request->s;
                $orders = Order::where('id', 'LIKE', '%' . $query . '%')
                    ->orWhere('id', 'LIKE', '%' . $query)
                    ->orWhere('id', 'LIKE', $query . '%')->paginate(10);

                return new OrderCollection($orders);
            }
        }

        $orders = Order::orderBy('created_at', 'desc')->paginate(10);
        $orders->load('fulls.product.images');
        return new OrderCollection($orders);
    }
    public function user(User $user)
    {
        $orders = $user->orders()->orderBy('created_at', 'desc')->get();
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
        if ((int)$request->input('status') == 2) {
            $notif = new OrderStoreConfirmationProvider($order->user->phone_number, $order->id, $order->user->name);
            $notif->send();
        } else if ((int)$request->input('status') == 1) {
            $notif = new OrderSuccessProvider($order->user->phone_number, $order->id, $order->user->name);
            $notif->send();
        }
        // $notif = new OrderStatusProvider($order->user->phone_number, __('orders.' . $request->input('status')));
        // $notif->send();
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
        $notif = new OrderPostalProvider($order->user->phone_number, $request->input('tracking_code'), $order->user->name, $order->id);
        $notif->send();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
}
