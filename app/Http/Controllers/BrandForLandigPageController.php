<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrandForLandingPageCollection;
use App\Models\BrandLanding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandForLandigPageController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required|integer|exists:brands,id|unique:brand_landings,brand_id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        BrandLanding::create([
            'brand_id' => $request->input('brand_id'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function delete(BrandLanding $brandLanding)
    {
        $brandLanding->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function index()
    {
        $brands = BrandLanding::all();
        return new BrandForLandingPageCollection($brands);
    }
}
