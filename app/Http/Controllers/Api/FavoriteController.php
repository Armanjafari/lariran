<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\FavoriteCollection;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $favorite = Favorite::where('user_id', auth()->id())->where('product_id', $request->input('product_id'))->get();
        if (!$favorite->isEmpty()) {
            auth()->user()->favorites()->where('product_id', $request->input('product_id'))->delete();
            return response()->json([
                'data' => [
                    'محصول از علاقه مندی ها حذف شد',
                    false,
                ],
                'status' => 'success',
            ]);
        }

        auth()->user()->favorites()->create([
            'product_id' => $request->input('product_id'),
        ]);
        return response()->json([
            'data' => [
                'محصول به علاقه مندی ها اضافه شد',
                true,
            ],
            'status' => 'success',
        ]);
    }
    public function index()
    {
        $favorites = auth()->user()->favorites;
        // dd($favorites);
        return new FavoriteCollection($favorites);
    }
    public function product(Product $product)
    {
        $favorite = auth()->user()->favorites()->where('product_id' , $product->id)->get();
        if (!$favorite->isEmpty()) {
            return response()->json([
                'data' => [true],
                'status' => 'success',

            ]);
        }
        return response()->json([
            'data' => [false],
            'status' => 'success',

        ]);
    }
}
