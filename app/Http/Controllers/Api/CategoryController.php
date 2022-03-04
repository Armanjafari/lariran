<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories,name|string|max:255|min:2',
            'persian_name' => 'required|min:2',
            'parent_id' => 'integer',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
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
            'status' => 'success',
        ]);
    }
    public function update(Request $request , Category $category)
    {
        $request->validate([
            'name' => 'required|unique:categories,name|string|max:255|min:2',
            'persian_name' => 'required|min:2',
            'parent_id' => 'required|integer'
        ]);
        $category->update([
            'name' => $request->input('name'),
            'persian_name' => $request->input('persian_name'),
            'parent_id' => $request->input('parent_id'),
        ]);
        return response()->json([
            'status' => 'success',
        ],200);
    }
    public function index()
    {
        $categories = Category::paginate(10);
        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }
}
