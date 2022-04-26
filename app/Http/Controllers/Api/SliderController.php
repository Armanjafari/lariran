<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\SliderCollection;
use App\Http\Resources\v1\SliderResource;
use App\Models\Slider;
use BadMethodCallException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin'])->except(['index']);
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'link' => 'required|url',
            'type' => 'required||in:main,top,left,right,bottom,bottom-right,bottom-left',
            'color' => 'string',
            'persian_name' => 'required|string',
            'image' => 'image|mimes:jpeg,jpg,png|max:512'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        // if (Slider::whereLink($request->input('link'))  ) {
        //     # code...
        // }
        $slider = Slider::create([
            'persian_name' => $request->input('persian_name'),
            'link' => $request->input('link'),
            'type' => $request->input('type'),
            'color' => $request->input('color') ?? null,
        ]);
        if ($request->hasFile('image') && !is_null($request->image)) {
            $this->image($request, $slider);
        }
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    private function image(Request $request, Slider $slider)
    {
        $image = $request->file('image');
        $destination = '/sliders/';
        $filename = date('mdYHis') . uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path($destination), $filename);
        $slider->image()->create([
            'address' => $destination . $filename
        ]);
    }
    public function update(Request $request, Slider $slider)
    {
        $validator = Validator::make($request->all(), [
            'link' => 'required|url',
            'type' => 'required|in:main,top,left,right,bottom,bottom-right,bottom-left',
            'color' => 'string',
            'imagee' => 'string',
            'persian_name' => 'required|string',
            'images' => 'image|mimes:jpeg,jpg,png|max:512'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $slider->update([
            'link' => $request->input('link'),
            'type' => $request->input('type'),
            'persian_name' => $request->input('persian_name'),
            'color' => $request->input('color') ?? null,
        ]);
        if ($request->hasFile('image') && !is_null($request->image)) {
            $this->deleteImage($slider);
            $this->image($request, $slider);
        }
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }
    private function deleteImage(Slider $slider)
    {
        File::delete(public_path() . $slider->image->address);
        $slider->image->delete();
    }
    public function index()
    {
        $sliders = Slider::all();
        return new SliderCollection($sliders);
    }
    public function delete(Slider $slider)
    {
        $slider->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }
    public function type($slider)
    {
        $slider = Slider::whereType($slider)->get();
        try {
            return new SliderCollection($slider);
        } catch (BadMethodCallException $e) {
            return new SliderResource($slider);
        }
    }
}
