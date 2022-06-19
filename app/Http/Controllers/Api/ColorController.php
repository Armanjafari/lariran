<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ColorCollection;
use App\Http\Resources\v1\ColorResource;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ColorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','role:admin'])->except(['index', 'single']);

    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:colors,title|string|max:255|min:2',
            'value' => 'required|max:255|min:2',
            'option_id' => 'required|exists:options,id',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        Color::create([
            'title' => $request->input('title'),
            'value' => $request->input('value'),
            'option_id' => $request->input('option_id'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request , Color $optionValue)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|min:2|unique:colors,title,' . $optionValue->id,
            'value' => 'required|max:255|min:2',
            'option_id' => 'required|exists:options,id',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $optionValue->update([
            'title' => $request->input('title'),
            'value' => $request->input('value'),
            'option_id' => $request->input('option_id'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function index()
    {
        $colors = Color::all();
        return new ColorCollection($colors);
    }
    public function delete(Color $optionValue)
    {
        $optionValue->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function single(Color $optionValue)
    {
        return new ColorResource($optionValue);
    }
}
