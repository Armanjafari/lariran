<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\SenderCollection;
use App\Http\Resources\v1\SenderResource;
use App\Models\Sender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SenderController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'address' => 'required|string',
            'phone_number' => 'required',
            'postal_code' => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        Sender::create([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'phone_number' => $request->input('phone_number'),
            'postal_code' => $request->input('postal_code'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }
    public function update(Request $request , Sender $sender)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'address' => 'required|string',
            'phone_number' => 'required',
            'postal_code' => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $sender->update([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'phone_number' => $request->input('phone_number'),
            'postal_code' => $request->input('postal_code'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }
    public function index()
    {
        $senders = Sender::all();
        return new SenderCollection($senders);
    }
    public function delete(Sender $sender)
    {
        $sender->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ], 200);
    }
    public function single(Sender $sender)
    {
        return new SenderResource($sender);
    }
}
