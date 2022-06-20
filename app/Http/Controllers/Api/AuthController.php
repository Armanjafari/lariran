<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['me', 'logout']);
        $this->middleware('throttle:5,5')->only(['register']); // TODO set captcha for everywhere that needed
        $this->middleware('throttle:5,5')->only(['login']);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed'
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error'
            ],400);
          }
        $user = User::create([
            'name' => $request->name,
            'password' => bcrypt($request->password),
            'email' => $request->email
        ]);
        return response()->json([
            'data' => [
                'token' => $user->createToken('tokens')->plainTextToken,
                'token_type' => 'Bearer'
            ],
            'status' => 'success'
        ]);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error'
            ],401);
        }
        $attr = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ]);
        if (!Auth::attempt($attr)) {
            return response()->json([
                'data' => 'نام کاربری یا رمز عبور اشتباه میباشد',
                'status' => 'error',
        ],401);
        }

        return response()->json([
            'data' => ['token' => auth()->user()->createToken('API Token')->plainTextToken],
            'status' => 'success'
        ]);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        session()->invalidate();
        auth()->guard('web')->logout();
        return response()->json([
            'data' => 'Tokens Revoked',
            'status' => 'success'

        ]);

    }
    public function me()
    {
        return response()->json([
            'data' => auth()->user()->load('roles'),
            'status' => 'success'
        ]);
    }
}
