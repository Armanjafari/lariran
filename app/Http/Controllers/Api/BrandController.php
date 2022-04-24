<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\BrandCollection;
use App\Http\Resources\v1\BrandResource;
use App\Http\Resources\v1\ProductCollection;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','role:admin'])->except(['index', 'single']);

    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:brands,name|string|max:255|min:2',
            'persian_name' => 'required|min:2',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        Brand::create([
            'name' => $request->input('name'),
            'persian_name' => $request->input('persian_name'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request , Brand $brand)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2|unique:brands,name,'. $brand->id, 
            'persian_name' => 'required|min:2',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $brand->update([
            'name' => $request->input('name'),
            'persian_name' => $request->input('persian_name'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function index()
    {
        $brands = Brand::paginate(10);
        return new BrandCollection($brands);
    }
    public function delete(Brand $brand)
    {
        $brand->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function single(Brand $brand)
    {
        return new BrandResource($brand);
    }
    public function products(Brand $brand)
    {
        return new ProductCollection($brand->products); // 
    }
}
