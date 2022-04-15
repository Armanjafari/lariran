<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\VarietyCollection;
use App\Http\Resources\v1\VarietyResource;
use App\Models\Full;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VarietyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','role:admin'])->except(['index', 'single']);

    }
    public function create(Request $request , Product $product)
    {
        $validator = Validator::make($request->all(), [
            'stock' => 'required|integer',
            'price' => 'required|integer',
            'show_price' => 'required|integer',
            'waranty_id' => 'required|integer|exists:waranties,id',
            'currency_id' => 'required|integer|exists:currencies,id',
            'color_id' => 'integer',
            'is_active' => 'integer',
            
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
          foreach ($product->fulls as $full) {
              if($full->color_id == $request->input('color_id') ?? null){
                return response()->json([
                    'data' => [
                        'error' => ['برای این محصول قبلا موجودی و قیمت وارد شده است']
                    ],
                    'status' => 'error',
                ]);
              }
          }
        //   dd('here');
          $product->fulls()->create([
            'stock' => $request->input('stock'),
            'price' =>  $request->input('price'),
            'show_price' =>  $request->input('show_price'),
            'waranty_id' =>  $request->input('waranty_id'),
            'currency_id' =>  $request->input('currency_id'),
            'color_id' =>  $request->input('color_id') ?? null,
            'is_active' =>  $request->input('is_active') ?? 1,
          ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request , Full $variety)
    {
        $validator = Validator::make($request->all(), [
            'stock' => 'required|integer',
            'price' => 'required|integer',
            'show_price' => 'required|integer',
            'waranty_id' => 'required|integer|exists:waranties,id',
            'product_id' => 'required|integer|exists:products,id',
            'currency_id' => 'required|integer|exists:currencies,id',
            'color_id' => 'integer',
            'is_active' => 'integer',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          } // TODO maybe we have a bug with color_id error
        $variety->update([
            'stock' => $request->input('stock'),
            'price' =>  $request->input('price'),
            'show_price' =>  $request->input('show_price'),
            'waranty_id' =>  $request->input('waranty_id'),
            'product_id' =>  $request->input('product_id'),
            'currency_id' =>  $request->input('currency_id'),
            'color_id' =>  $request->input('color_id') ?? null,
            'is_active' =>  $request->input('is_active') ?? 1,
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function index(Product $product)
    {
        $vareities = $product->fulls()->paginate(10);
        return new VarietyCollection($vareities);
    }
    public function delete(Full $variety)
    {
        $variety->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function single(Full $variety)
    {
        return new VarietyResource($variety); // TODO currency validation with admin should check
    }
}
