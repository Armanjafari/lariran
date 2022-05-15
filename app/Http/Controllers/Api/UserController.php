<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\CityCollection;
use App\Http\Resources\v1\ProvinceCollection;
use App\Http\Resources\v1\ShippingCollection;
use App\Http\Resources\v1\ShippingResource;
use App\Models\Province;
use App\Models\Shiping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->except(['province' , 'city']);

    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required',
            'postal_code' => 'required|integer',
            'city_id' => 'required|integer|exists:cities,id',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        //   auth()->user()->shipings()-
        Shiping::create([
            'address' => $request->input('address'),
            'postal_code' => $request->input('postal_code'),
            'city_id' => $request->input('city_id'),
            'user_id' => auth()->id(),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request , Shiping $address)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required',
            'postal_code' => 'required',
            'city_id' => 'required|exists:cities,id',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $address->update([
            'address' => $request->input('address'),
            'postal_code' => $request->input('postal_code'),
            'city_id' => $request->input('city_id'),
            'user_id' => auth()->id(),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function index()
    {
        $shippings = Shiping::all();
        return new ShippingCollection($shippings);
    }
    public function delete(Shiping $address)
    {
        $address->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function single(Shiping $address)
    {
        return new ShippingResource($address); // TODO currency validation with admin should check
    }
    public function province()
    {
        $provinces = Province::all();
        return new ProvinceCollection($provinces);
    }

    public function city(Province $province)
    {
        $cities = $province->cities;
        return new CityCollection($cities);
    }
}
