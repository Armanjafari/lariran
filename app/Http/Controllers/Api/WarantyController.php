<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\WarantyCollection;
use App\Http\Resources\v1\WarantyResource;
use App\Models\Waranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarantyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum','role:admin'])->except(['index', 'single']);

    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:waranties,name|string|max:255|min:2',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        Waranty::create([
            'name' => $request->input('name'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function update(Request $request , Waranty $waranty)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2|unique:waranties,name,' . $waranty->id,
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $waranty->update([
            'name' => $request->input('name'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function index()
    {
        $waranties = Waranty::paginate(10);
        return new WarantyCollection($waranties);
    }
    public function delete(Waranty $waranty)
    {
        $waranty->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function single(Waranty $waranty)
    {
        return new WarantyResource($waranty);
    }
}
