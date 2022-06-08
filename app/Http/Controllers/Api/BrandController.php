<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\BrandCollection;
use App\Http\Resources\v1\BrandResource;
use App\Http\Resources\v1\ProductCollection;
use App\Http\Resources\v1\ProductForCategoriesCollection;
use App\Models\Brand;
use App\Models\Full;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin'])->except(['index', 'single','all']);
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:brands,name|string|max:255|min:2',
            'persian_name' => 'required|min:2',
            'image' => 'image|mimes:jpeg,jpg,png|max:512'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $brand = Brand::create([
            'name' => $request->input('name'),
            'persian_name' => $request->input('persian_name'),
        ]);
        if ($request->hasFile('image') && !is_null($request->image)) {
            $this->image($request, $brand);
        }
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request, Brand $brand)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2|unique:brands,name,' . $brand->id,
            'persian_name' => 'required|min:2',
            'image' => 'image|mimes:jpeg,jpg,png|max:512'
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
        if ($request->hasFile('image') && !is_null($request->image)) {
            $this->deleteImage($brand);
            $this->image($request, $brand);
        }
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }
    private function image(Request $request, Brand $brand)
    {
        $image = $request->file('image');
        $destination = '/brand/';
        $filename = date('mdYHis') . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path($destination), $filename);
        $brand->image()->create([
            'address' => $destination . $filename
        ]);
    }
    public function index()
    {
        $brands = Brand::paginate(10);
        return new BrandCollection($brands);
    }
    public function all()
    {
        $brands = Brand::all();
        return new BrandCollection($brands);
    }
    private function deleteImage(Brand $brand)
    {
        $image = $brand->image;
        File::delete(public_path() . $image->address);
        $image->delete();
    }
    public function delete(Brand $brand)
    {
        $this->deleteImage($brand);
        $brand->image->delete();
        $brand->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }
    public function single(Brand $brand)
    {
        return new BrandResource($brand);
    }
    public function products(Brand $brand, Request $request)
    {
        // return new ProductCollection($brand->products);
        $validator = Validator::make($request->all(), [
            'sort' => 'integer',
            'min' => 'integer',
            'max' => 'integer',
            'stock' => 'boolean',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $products = $brand->products;
        if (($request->input('sort') >= 1 && $request->input('sort') <= 3)) {
            $pro = [];
            foreach ($products as $product) {
                $product->load('fulls');
                foreach ($product->fulls as $full) {
                    array_push($pro, $full);
                }
            }
            $ids = [];
            foreach ($pro as $value) {
                array_push($ids, $value->id);
            }
            $proz = Full::whereIn('id', array_values($ids));

            $newway = [];
            foreach ($proz->get() as $key => $full) {
                $tt = [];
                $tt['id'] = $full->product_id;
                $tt['title'] = $full->product->title;
                $tt['persian_title'] = $full->product->persian_title;
                $tt['slug'] = $full->product->slug;
                $tt['stock'] = $full->stock;
                $tt['image'] = $full->product->images->first()->address ?? null;
                $tt['price'] = $full->price * $full->currency->value;
                $tt['show_price'] = $full->show_price * $full->currency->value;
                $tt['percent'] = $full->percentage();
                array_push($newway, $tt);
            }
            $newway = collect($newway);
            if (isset($request->min)) {
                $newway = $newway->where('price', '>=', $request->min);
            }
            if (isset($request->max)) {
                $newway = $newway->where('price', '<=', $request->max);
            }
            if ($request->stock) {
                $newway = $newway->where('stock', '>', 0);
            }
            if ($request->input('sort') == 1) {
                $newway = $newway->sortByDesc('price');
            } elseif ($request->input('sort') == 2) {
                $newway = $newway->sortBy('price');
            } elseif ($request->input('sort') == 3 || !$request->input('sort')) {
                $newway = $newway->sortBy('created_at');
            }

            $newway = $newway->unique('id');
            $newway = collect($newway->values());
            $paginator = new LengthAwarePaginator($newway, count($newway), 10);
            return new ProductForCategoriesCollection($paginator);
        } else {
            $products = collect($products);
            $products = $products->sortByDesc('views');
            $fulls = [];
            foreach ($products as $product) {
                if (!is_null($product->fulls)) {

                    array_push($fulls, $product->fulls->first());
                }
            }
            $newway = [];
            foreach ($fulls as $full) {
                if (!is_null($full)) {
                    $tt = [];
                    $tt['id'] = $full->product_id;
                    $tt['title'] = $full->product->title;
                    $tt['persian_title'] = $full->product->persian_title;
                    $tt['slug'] = $full->product->slug;
                    $tt['stock'] = $full->stock;
                    $tt['image'] = $full->product->images->first()->address ?? null;
                    $tt['price'] = $full->price * $full->currency->value;
                    $tt['show_price'] = $full->show_price * $full->currency->value;
                    $tt['percent'] = $full->percentage();
                    array_push($newway, $tt);
                }
            }
            $newway = collect($newway);
            if (isset($request->min)) {
                $newway = $newway->where('price', '>=', $request->min);
            }
            if (isset($request->max)) {
                $newway = $newway->where('price', '<=', $request->max);
            }
            if ($request->stock) {
                $newway = $newway->where('stock', '>', 0);
            }
            $paginator = new LengthAwarePaginator($newway, count($newway), 10);
            return new ProductForCategoriesCollection($paginator);
        }
    }
}
