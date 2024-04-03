<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AttributeCollection;
use App\Http\Resources\v1\BrandResource;
use App\Http\Resources\v1\CategoryResource;
use App\Http\Resources\v1\ImageCollection;
use App\Http\Resources\v1\OptionResource;
use App\Http\Resources\v1\ProductByCategoriesCollection;
use App\Http\Resources\v1\ProductCollection;
use App\Http\Resources\v1\ProductForCategoriesCollection;
use App\Http\Resources\v1\ProductResource;
use App\Http\Resources\v1\VarietyCollection;
use App\Models\Category;
use App\Models\Full;
use App\Models\Image;
use App\Models\Product;
use GuzzleHttp\Client;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
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
            'show_weight' => 'integer',
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
            'show_weight' => $request->input('show_weight') ?? '',
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
            'show_weight' => 'integer',
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
            'show_weight' => $request->input('show_weight') ?? '',
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
        if ($request->has('category_id')) {
            if (!is_null($request->input('category_id'))) {
                return $this->byCategory($request);
            }
        }
        $products = Product::orderBy('created_at', 'desc')->get();
        return new ProductCollection($products->paginate(10));
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
    private function byCategory(Request $request)
    {
        $category = Category::find($request->category_id);
        $products = $category->products;
        // dd($products);
        // foreach ($category->child as $category) {
        //     $products = $products->push($category->products()->get());
        //     foreach ($category->child as $category) {
        //         $products = $products->push($category->products()->get());
        //         foreach ($category->child as $category) {
        //             $products = $products->push($category->products()->get());
        //         }
        //     }
        // }
        // $products = Arr::flatten($products);
        // dd($products);
        foreach ($products as $product) {
            $product->load('fulls');
        }
        $products = collect($products);
        $fulls = [];
        foreach ($products as $product) {
            if (!is_null($product->fulls)) {

                array_push($fulls, $product->fulls->first());
            }
        }
        // dd($fulls);
        $newway = [];
        foreach ($fulls as $full) {
            if (!is_null($full)) {
                $tt = [];
                $tt['id'] = $full->product_id;
                $tt['title'] = $full->product->title;
                $tt['persian_title'] = $full->product->persian_title;
                $tt['slug'] = $full->product->slug;
                $tt['category_id'] = new CategoryResource($full->product->category);
                $tt['brand_id'] = new BrandResource($full->product->brand);
                if(!is_null($full->product->option)){
                    $tt['option_id'] = new OptionResource($full->product->option);
                    $tt['stock'] = $full->stock;
                } else{
                    $tt['option_id'] = null;
                    $tt['stock'] = $full->stock ?? null;
                }
                $tt['description'] = $full->product->description;
                $tt['weight'] = $full->product->weight;
                $tt['show_weight'] = $full->product->show_weight;
                $tt['attributes'] = new AttributeCollection($full->product->attributes);
                $tt['keywords'] = $full->product->keywords;
                $tt['status'] = $full->product->status;
                $tt['image'] = $full->product->images;
                $tt['price'] = $full->price * $full->currency->value;
                $tt['show_price'] = $full->show_price * $full->currency->value;
                $tt['varieties'] = new VarietyCollection($full->product['fulls']);
                $tt['stock'] = $full->stock;
                // $tt['percent'] = $full->percentage();
                // $tt['created_at'] = $full->created_at;
                array_push($newway, $tt);
            }
        }
        $newway = collect($newway);
        if ($request->has('stock') && (!is_null($request->input('stock')))) {
            $newway = $newway->where('stock', '>', 0);
        }
        // dd($tt['category_id']);



        return new ProductByCategoriesCollection($newway->paginate(10));

        //  $products = $category->products;
        //  return new ProductCollection($products);
    }
}
