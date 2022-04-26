<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AttributeValueCollection;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttributeValueController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','role:admin'])->except(['index']);

    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|unique:attribute_values,value|string|min:2',
            'product_id' => 'required|exists:products,id',
            'attribute_id' => 'required|exists:attributes,id',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        AttributeValue::create([
            'value' => $request->input('value'),
            'product_id' => $request->input('product_id'),
            'attribute_id' => $request->input('attribute_id'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request , AttributeValue $attributeValue)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|min:2|unique:attribute_values,value,'. $attributeValue->id, 
            'product_id' => 'required|exists:products,id',
            'attribute_id' => 'required|exists:attributes,id',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $attributeValue->update([
            'value' => $request->input('value'),
            'product_id' => $request->input('product_id'),
            'attribute_id' => $request->input('attribute_id'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function index()
    {
        $attribute_values = AttributeValue::paginate(10);
        return new AttributeValueCollection($attribute_values);
    }
    public function delete(AttributeValue $attributeValue)
    {
        $attributeValue->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function product(Product $product)
    {
        $attributes = $product->attributes;

        return new AttributeValueCollection($attributes);
    }
}
