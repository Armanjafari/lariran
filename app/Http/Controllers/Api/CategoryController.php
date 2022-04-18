<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\CategoryCollection;
use App\Http\Resources\v1\CategoryResource;
use App\Http\Resources\v1\ProductCollection;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','role:admin'])->except(['index', 'single' , 'products']);

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
    public function update(Request $request , Category $category)
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
        ],200);
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
        ],200);
    }
    public function single(Category $category)
    {
        return new CategoryResource($category);
    }
    public function products(Category $category , Request $request)
    {
        return new ProductCollection($category->products()->paginate(10));
    }
}
