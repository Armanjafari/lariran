<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ColorCollection;
use App\Http\Resources\v1\OptionCollection;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','role:admin']);

    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:options,name|string|max:255|min:2',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        Option::create([
            'name' => $request->input('name'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request , Option $option)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2|unique:options,name,' . $option->id,
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $option->update([
            'name' => $request->input('name'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function index()
    {
        $options = Option::all();
        return new OptionCollection($options);
    }
    public function delete(Option $option)
    {
        try {
            $option->delete();
            return response()->json([
                'data' => [],
                'status' => 'success',
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => [
                    'option' => ['محصول یا مقداری به این اپشن ارتباط دارد ابتدا ارتباطات را تغییر دهید'],
                ],
                'status' => 'error',
            ], 200);
        }
    }
    public function optionValue(Option $option)
    {
        return new ColorCollection($option->values);
    }
}
