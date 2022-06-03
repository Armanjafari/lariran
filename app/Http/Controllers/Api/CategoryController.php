<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\CategoryCollection;
use App\Http\Resources\v1\CategoryResource;
use App\Http\Resources\v1\ProductForCategoriesCollection;
use App\Models\Category;
use App\Models\Full;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
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
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        // $request->validate([
        //     'name' => 'required|unique:categories,name|string|max:255|min:2',
        //     'persian_name' => 'required|min:2',
        //     'parent_id' => 'integer',
        // ]);
        Category::create([
            'name' => $request->input('name'),
            'persian_name' => $request->input('persian_name'),
            'parent_id' => $request->input('parent_id') ?? 0,
        ]);
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
            'parent_id' => 'required|integer'
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
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
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
    public function delete(Category $category)
    {
        $category->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
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
        if (($request->input('sort') >= 1 && $request->input('sort') <= 3) || !$request->input('sort')) {
            $products = $category->products;
            // dd($products);
            // array_push($products, $category->products);
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

            // dd($products);
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
                $newway = $newway->sortByDesc('created_at');
            }


            // $proz->get()->unique('product_id');
            // $proz = $proz->get()->toArray();
            $newway = $newway->unique('id');
            $newway = collect($newway->values());
            $paginator = new LengthAwarePaginator($newway, count($newway), 10);
            return new ProductForCategoriesCollection($paginator); // $category->products()->paginate(10)
        }
    }
}
