<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\FileHasExistsException;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\CategoryCollection;
use App\Http\Resources\v1\CategoryResource;
use App\Http\Resources\v1\ProductForCategoriesCollection;
use App\Models\Category;
use App\Models\Full;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin'])->except(['index', 'single', 'products']);
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories,name|string|max:255|min:2',
            'persian_name' => 'required|min:2',
            'parent_id' => 'integer',
            'image' => 'image|mimes:jpeg,webp,jpg,png|max:512'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $category = Category::create([
            'name' => $request->input('name'),
            'persian_name' => $request->input('persian_name'),
            'parent_id' => $request->input('parent_id') ?? 0,
        ]);
        if ($request->hasFile('image') && !is_null($request->image)) {
            $this->image($request, $category);
        }
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2|unique:categories,name,' . $category->id,
            'persian_name' => 'required|min:2',
            'parent_id' => 'required|integer',
            'image' => 'image|mimes:jpeg,webp,jpg,png|max:512'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $category->update([
            'name' => $request->input('name'),
            'persian_name' => $request->input('persian_name'),
            'parent_id' => $request->input('parent_id'),
        ]);
        if ($request->hasFile('image') && !is_null($request->image)) {
            $this->deleteImage($category);
            $this->image($request, $category);
        }
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }
    private function image(Request $request, Category $category)
    {
        $image = $request->file('image');
        $destination = '/cate$category/';
        $filename = date('mdYHis') . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path($destination), $filename);
        $category->image()->create([
            'address' => $destination . $filename
        ]);
    }
    public function index()
    {
        $categories = Category::all();
        return new CategoryCollection($categories);
        // return response()->json([
        //     'status' => 'success',
        //     'data' => $categories,
        // ]);
    }
    private function deleteImage(Category $category)
    {
        $image = $category->image;
        if (is_null($image)) {
            return false;
        }
        File::delete(public_path() . $image->address);
        $image->delete();
    }
    public function delete(Category $category)
    {
        try {
            $this->deleteImage($category);
            $category->delete();
            return response()->json([
                'data' => [],
                'status' => 'success',
            ], 200);
        } catch (FileHasExistsException $e) {
            return response()->json([
                'data' => [
                    'category' => ['محصول یا فرزندی برای این دسته بندی وجود دارد ابتدا محصول یا فرزند را به دسته بندی دیگری ارتباط دهید'],
                ],
                'status' => 'error',
            ], 200);
        }
    }
    public function single(Category $category)
    {
        return new CategoryResource($category);
    }
    public function products(Category $category, Request $request)
    {
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
        $products = $category->products;
        foreach ($category->child as $category) {
            $products = $products->push($category->products()->get());
            foreach ($category->child as $category) {
                $products = $products->push($category->products()->get());
                foreach ($category->child as $category) {
                    $products = $products->push($category->products()->get());
                }
            }
        }
        $products = Arr::flatten($products);

        foreach ($products as $product) {
            $product->load('fulls');
        }
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
                $tt['created_at'] = $full->created_at;
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
            } elseif ($request->input('sort') == 3) {
                $newway = $newway->sortByDesc('created_at');
            }
            $newway = $newway->unique('id');
            $newway = collect($newway->values());
            $paginator = new LengthAwarePaginator($newway, count($newway), 10);
            return new ProductForCategoriesCollection($paginator); // $category->products()->paginate(10)
        } else {
            $products = collect($products);
            $products = $products->sortByDesc('views');
            // dd($products);
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
                    $tt['created_at'] = $full->created_at;
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
