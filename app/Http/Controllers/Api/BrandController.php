<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\BrandCollection;
use App\Http\Resources\v1\BrandResource;
use App\Http\Resources\v1\ProductCollection;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','role:admin'])->except(['index', 'single']);

    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:brands,name|string|max:255|min:2',
            'persian_name' => 'required|min:2',
            'image' => 'image|mimes:jpeg,jpg,png|max:512'
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $brand = Brand::create([
            'name' => $request->input('name'),
            'persian_name' => $request->input('persian_name'),
        ]);
        if ($request->hasFile('image') && !is_null($request->image)) {
            $this->image($request, $brand);
        }
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request , Brand $brand)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2|unique:brands,name,'. $brand->id, 
            'persian_name' => 'required|min:2',
            'image' => 'image|mimes:jpeg,jpg,png|max:512'
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $brand->update([
            'name' => $request->input('name'),
            'persian_name' => $request->input('persian_name'),
        ]);
        if ($request->hasFile('image') && !is_null($request->image)) {
            $this->deleteImage($brand);
            $this->image($request, $brand);
        }
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    private function image(Request $request, Brand $brand)
    {
        $image = $request->file('image');
            $destination = '/brand/';
            $filename = date('mdYHis') . uniqid() . '.' .$image->getClientOriginalExtension();
            $image->move(public_path($destination), $filename);
            $brand->image()->create([
                'address' => $destination . $filename
            ]);
    }
    public function index()
    {
        $brands = Brand::paginate(10);
        return new BrandCollection($brands);
    }
    private function deleteImage(Brand $brand)
    {
            $image = $brand->image;
            File::delete(public_path() . $image->address);
            $image->delete();
    }
    public function delete(Brand $brand)
    {
        $this->deleteImage($brand);
        $brand->image->delete();
        $brand->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function single(Brand $brand)
    {
        return new BrandResource($brand);
    }
    public function products(Brand $brand)
    {
        return new ProductCollection($brand->products); // 
    }
}
