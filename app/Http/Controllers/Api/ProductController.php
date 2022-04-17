<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ProductCollection;
use App\Http\Resources\v1\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','role:admin'])->except(['index', 'single']);

    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:products,title',
            'persian_title' => 'required',
            'category_id' => 'required|exists:categories,id',
            'slug' => 'required|unique:products,slug',
            'brand_id' => 'required|exists:brands,id',
            'option_id' => 'integer|exists:options,id',
            'description' => 'required',
            'weight' => 'required',
            'keywords' => 'string',
            'status' => 'integer',
            'images.*' => 'image|mimes:jpeg,jpg,png|max:512' // 
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $product = Product::create([
            'name' => $request->input('name'),
            'title' => $request->input('title'),
            'persian_title' => $request->input('persian_title'),
            'category_id' => $request->input('category_id'),
            'slug' => $request->input('slug'),
            'brand_id' => $request->input('brand_id'),
            'option_id' => $request->input('option_id') ?? null,
            'description' => $request->input('description'),
            'weight' => $request->input('weight'),
            'keywords' => $request->input('keywords') ?? '',
            'status' => $request->input('status') ?? 1,
        ]);
        if ($request->hasFile('images') && !is_null($request->images)) {
            $this->image($request, $product);
        }
        //  else if ($request->has('main')){
        //     $this->image($request, $product);
        // }
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    private function image(Request $request, Product $product)
    {
        // $i = 1;
        $images = $request->file('images');
        // dd($images);
        foreach ($images as $image) {
            // dd('here');
            $destination = '/images/' . now()->year . '/' . now()->month . '/' . now()->day . '/';
            $filename = date('mdYHis') . uniqid() . '.' .$image->getClientOriginalExtension();
            $image->move(public_path($destination), $filename);
            $product->images()->create([
                'address' => $destination . $filename
            ]);
            // $i++;
        }
    }
    public function update(Request $request , Product $product)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'persian_title' => 'required',
            'category_id' => 'required',
            'slug' => 'required',
            'brand_id' => 'required',
            'option_id' => 'integer|exists:options,id',
            'description' => 'required',
            'weight' => 'required',
            'keywords' => 'string',
            'status' => 'integer',
            'images' => 'image|mimes:jpeg,jpg,png|max:512'
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $product->update([
            'name' => $request->input('name'),
            'title' => $request->input('title'),
            'persian_title' => $request->input('persian_title'),
            'category_id' => $request->input('category_id'),
            'slug' => $request->input('slug'),
            'brand_id' => $request->input('brand_id'),
            'option_id' => $request->input('option_id') ?? null,
            'description' => $request->input('description'),
            'weight' => $request->input('weight'),
            'keywords' => $request->input('keywords') ?? '',
            'status' => $request->input('status') ?? 1,
        ]);
        if ($request->has('images')) {
            $this->deleteImage($product);
            $this->image($request, $product);
        }
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    private function deleteImage(Product $product)
    {
        foreach ($product->images as $image) {
            File::delete($image->address);
            $image->delete();
        }
    }
    public function index()
    {
        $products = Product::paginate(10);
        return new ProductCollection($products);
    }
    public function delete(Product $product)
    {
        $product->delete();
        $this->deleteImage($product);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function single(Product $product)
    {
        return new ProductResource($product);
    }
}
