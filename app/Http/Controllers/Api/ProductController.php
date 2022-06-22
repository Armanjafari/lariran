<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ImageCollection;
use App\Http\Resources\v1\ProductCollection;
use App\Http\Resources\v1\ProductResource;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin'])->except(['index', 'single', 'relateds', 'productImages']);
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:products,title',
            'persian_title' => 'required',
            'slug' => 'required',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'option_id' => 'required|integer|exists:options,id',
            'description' => 'required',
            'weight' => 'required|integer',
            'keywords' => 'string',
            'status' => 'integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $request->slug = str_replace(' ', '-', $request->slug);
        $validator2 = Validator::make(['slug' => $request->slug], [
            'slug' => 'unique:products,slug',
        ]);

        if ($validator2->fails()) {
            return response()->json([
                'data' => $validator2->errors(),
                'status' => 'error',
            ]);
        }

        $product = Product::create([
            'name' => $request->input('name'),
            'title' => $request->input('title'),
            'persian_title' => $request->input('persian_title'),
            'category_id' => $request->input('category_id'),
            'slug' => str_replace(' ', '-', $request->slug),
            'brand_id' => $request->input('brand_id'),
            'option_id' => $request->input('option_id') ?? null,
            'description' => $request->input('description'),
            'weight' => $request->input('weight') ?? 0,
            'keywords' => $request->input('keywords') ?? '',
            'status' => $request->input('status') ?? 1,
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:products,title,' . $product->id,
            'persian_title' => 'required',
            'category_id' => 'required',
            'slug' => 'required',
            'brand_id' => 'required',
            'option_id' => 'integer|exists:options,id',
            'description' => 'required',
            'weight' => 'required|integer',
            'keywords' => 'string',
            'status' => 'integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $request->slug = str_replace(' ', '-', $request->slug);
        $validator2 = Validator::make(['slug' => $request->slug], [
            'slug' => 'unique:products,slug,' . $product->id,
        ]);

        if ($validator2->fails()) {
            return response()->json([
                'data' => $validator2->errors(),
                'status' => 'error',
            ]);
        }
        $product->update([
            'name' => $request->input('name'),
            'title' => $request->input('title'),
            'persian_title' => $request->input('persian_title'),
            'category_id' => $request->input('category_id'),
            'slug' => str_replace(' ', '-', $request->slug),
            'brand_id' => $request->input('brand_id'),
            'option_id' => $request->input('option_id') ?? null,
            'description' => $request->input('description'),
            'weight' => $request->input('weight') ?? 0,
            'keywords' => $request->input('keywords') ?? '',
            'status' => $request->input('status') ?? 1,
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }

    public function index(Request $request)
    {
        if ($request->has('s')) {
            if (!is_null($request->input('s'))) {
                $query = $request->s;
                $products = Product::where('persian_title', 'LIKE', '%' . $query . '%')
                    ->orWhere('persian_title', 'LIKE', '%' . $query)
                    ->orWhere('persian_title', 'LIKE', $query . '%')
                    ->orWhere('title', 'LIKE', '%' . $query . '%')
                    ->orWhere('title', 'LIKE', $query . '%')
                    ->orWhere('title', 'LIKE', '%' . $query)->paginate(10);

                return new ProductCollection($products);
            }
        }
        $products = Product::orderBy('created_at', 'desc')->paginate(10);
        return new ProductCollection($products);
    }
    public function delete(Product $product)
    {
        $this->deleteAllImages($product);
        $product->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }
    private function deleteAllImages(Product $product)
    {
        if (is_null($product->images))
            return false;
        foreach ($product->images as $image) {
            $image->delete();
        }
        return true;
    }
    public function single(Product $product)
    {
        $product->views += 1;
        $product->save();
        return new ProductResource($product);
    }
    public function relateds(Product $product)
    {
        $products = Product::limit(10)->where('category_id', '=', $product->category_id)->where('id', '!=', $product->id)->get();
        return new ProductCollection($products);
    }
    public function imageCreate(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'image|mimes:jpeg,jpg,png|max:512'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $image = $request->file('image');
        $destination = '/images/' . now()->year . '/' . now()->month . '/' . now()->day . '/';
        $filename = date('mdYHis') . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path($destination), $filename);
        $product->images()->create([
            'address' => $destination . $filename
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }
    public function imageDescCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'image|mimes:jpeg,jpg,png|max:1024'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $image = $request->file('image');
        $destination = '/desc-ckeditor/';
        $filename = date('mdYHis') . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path($destination), $filename);
        return response()->json([
            'data' => ['image' => 'https://api.lariran.com' . $destination . $filename],
            'status' => 'success',
        ], 200);
    }
    public function imageDelete(Image $image)
    {
        File::delete(public_path() . $image->address);
        $image->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }
    public function productImages(Product $product)
    {
        return new ImageCollection($product->images()->where('type', null)->get());
    }
}
