<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\CouponCollection;
use App\Models\Coupon;
use App\Support\Discount\Coupon\CouponValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    private $validator;
    public function __construct(CouponValidator $validator)
    {

        $this->middleware('auth:sanctum');
        $this->middleware('role:admin')->except(['store']);
        $this->validator = $validator;
    }
    public function store(Request $request)
    {
            // validate coupon
        try {
            $request->validate([
                'coupon' => ['required','exists:coupons,code']
            ]);
            // can user use it
            // put coupon into session
            $coupon = Coupon::where('code' , $request->coupon)->firstOrFail();
            $this->validator->isValid($coupon);
            session()->put(['coupon' => $coupon]);
            // redirect
            return response()->json([
                'data' => [],
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            // dd($e);
            return response()->json([
                'data' => [
                    'کد تخفیف نامعتبر می باشد '
                ],
                'status' => 'error'
            ]);
            // return redirect()->back()->withErrors(' کد تخفیف نامعتبر می باشد ');
        }
    }
    public function remove()
    {
        session()->forget('coupon');
        return response()->json([
            'data' => [],
            'status' => 'success'
        ]);
    }
    public function create(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'percent' => 'required|integer',
            'limit' => 'required',
            'type' => 'required',
            'type_id' => 'required',
            'expire_time' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }

        if ($request->type == 'user'){
            $type = 'App\User';            
        }else if ($request->type == 'product') {
            $type = 'App\Product';            

        }
        Coupon::create([
            'code' => $request->input('code'),
            'percent' => $request->input('percent'),
            'limit' => $request->input('limit'),
            'expire_time' => $request->input('expire_time'),
            'couponable_type' => $type,
            'couponable_id' => $request->type_id,
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function index()
    {
        $coupons = Coupon::paginate(10);
        return new CouponCollection($coupons);
    }
}
