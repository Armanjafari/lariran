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
            if (!is_null($request->input('status')) && is_numeric($request->input('status'))) {
                $payments = Payment::where('status', (int)$request->input('status'))->orderBy('created_at', 'desc');
                if ($request->has('s')) {
                    if (!is_null($request->input('s')) && !empty($request->input('s'))) {
                        $query = $request->input('s');
                        $payments = Payment::where('status', (int)$request->input('status'))
                            ->whereRelation('order', 'id', 'LIKE', '%' . $query . '%')
                            ->orderBy('created_at', 'desc');
                    }
                }
                if ($request->has('from') && $request->has('to')) {
                    if (!is_null($request->input('from')) && !empty($request->input('from')) && !is_null($request->input('to')) && !empty($request->input('to'))) {
                        $from = date($request->input('from'));
                        $to = date('Y/m/d', strtotime($request->input('to') . " +1 day"));
                        $payments->whereBetween('created_at', [$from, $to])->orderBy('created_at', 'desc');
                    }
                }
                return new OrderAdminByStatusCollection($payments->paginate(10));
            }
        }

        $orders = Order::with('payment.result')->orderBy('created_at', 'desc');
        // TODO this line throw an exception after changing the place of code without any reason !!! $orders->load('fulls.product.images');
        if ($request->has('s')) {
            if (!is_null($request->input('s')) && !empty($request->input('s'))) {
                $query = $request->s;
                $orders = Order::with('payment.result')->where('id', 'LIKE', '%' . $query . '%');
            }
        }
        if ($request->has('from') && $request->has('to')) {
            if (!is_null($request->input('from')) && !empty($request->input('from')) && !is_null($request->input('to')) && !empty($request->input('to'))) {
                $from = date($request->input('from'));
                $to = date('Y/m/d', strtotime($request->input('to') . " +1 day"));
                $orders->whereBetween('created_at', [$from, $to])->orderBy('created_at', 'desc');
            }
        }

        return new OrderCollection($orders->paginate(10));
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
