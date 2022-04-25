<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AttributeCollection;
use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttributeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','role:admin'])->except(['index']);

    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:attributes,name|string|min:2',
            'category_id' => 'required|exists:categories,id',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        Attribute::create([
            'name' => $request->input('name'),
            'category_id' => $request->input('category_id'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request , Attribute $attribute)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|unique:attributes,name,'. $attribute->id, 
            'category_id' => 'required|exists:categories,id',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $attribute->update([
            'name' => $request->input('name'),
            'category_id' => $request->input('category_id'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function index()
    {
        $attributes = Attribute::paginate(10);
        return new AttributeCollection($attributes);
    }
    public function delete(Attribute $attribute)
    {
        $attribute->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function category(Category $category)
    {
        $attributes = $category->attributes;
        return new AttributeCollection($attributes);
    }
}
