<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\CategoryCollection;
use App\Http\Resources\v1\CategoryResource;
use App\Http\Resources\v1\ProductCollection;
use App\Models\Category;
use App\Models\Full;
use App\Models\Product;
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

        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
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
        $paginator = new LengthAwarePaginator($products,count($products),10);
        // dd($paginator);
        //   $pro = [];
        //   $proz = new Full();
        //   foreach ($category->products as $product) {
        //     $product->load('fulls');

        //     foreach ($product->fulls as $full) {
        //         array_push($pro , $full);
        //     }
        // }
        // $proz = $proz->fill($pro);
        //   if ($request->input('sort') == 1) {
        //     $proz = $proz->orderBy('price' , 'asc');
        // }elseif ($request->input('sort') == 2) {
        //     $proz = $proz->orderBy('price' , 'desc');

        // }
        // if (isset($request->min)) {
        //     $proz = $proz->having('price','<=' , $request->min);
        // }

        // $proz = $proz->get()->unique('product_id');
        // $products = [];
        // foreach ($proz as $full) {
        //         array_push($products, $full->product);
        //     }
        return new ProductCollection($paginator); // $category->products()->paginate(10)
    }
}
