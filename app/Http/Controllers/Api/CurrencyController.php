<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\CurrencyCollection;
use App\Http\Resources\v1\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','role:admin'])->except(['index', 'single']);

    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:waranties,name|string|max:255|min:2',
            'persian_name' => 'required|string|max:255|min:2',
            'value' => 'required',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        Currency::create([
            'name' => $request->input('name'),
            'persian_name' => $request->input('persian_name'),
            'value' => $request->input('value'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request , Currency $currency)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2|unique:waranties,name,' . $currency->id,
            'persian_name' => 'required|string|max:255|min:2',
            'value' => 'required',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $currency->update([
            'name' => $request->input('name'),
            'persian_name' => $request->input('persian_name'),
            'value' => $request->input('value'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function index()
    {
        $currencies = Currency::paginate(10);
        return new CurrencyCollection($currencies);
    }
    public function delete(Currency $currency)
    {
        try {
            $currency->delete();
            return response()->json([
                'data' => [],
                'status' => 'success',
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => [
                    'currency' => ['محصولی به این واحد ارتباط دارد ابتدا محصول را به واحد دیگری ارتباط دهید'],
                ],
                'status' => 'error',
            ], 200);
        }
    }
    public function single(Currency $currency)
    {
        return new CurrencyResource($currency); // TODO currency validation with admin should check
    }
}
