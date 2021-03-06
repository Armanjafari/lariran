<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\v1\SendSmsCode;
use App\Models\Code;
use App\Models\User;
use App\Services\Convert\convertEnglishToPersian;
use App\Services\Notifications\Notification;
use App\Services\Notifications\Providers\SmsProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthWithCodeController extends Controller
{
    public $code;
    public function __construct()
    {
        // $this->middleware('guest');
        // $this->middleware('auth:sanctum')->only(['me', 'logout']);
        $this->middleware('throttle:5,5')->only(['register']); // TODO set captcha for everywhere that needed
        $this->middleware('throttle:5,5')->only(['login']);
        $this->middleware('throttle:5,5')->only(['checkLogin']);
    }
    public function checkLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $phone_number = $this->convertor($request->input('phone_number'));
        $user = User::where('phone_number', $phone_number)->first();
        if (!$user) {
            return response()->json([
                'data' => [
                    'action' => 'register',
                    'phone_number' => $phone_number,
                ],
                'status' => 'success',
            ]);
        }
        $this->sendSms($phone_number);
        return response()->json([
            'data' => [
                'action' => 'login',
                'phone_number' => $phone_number,
            ],
            'status' => 'success',
        ]);
    }
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|exists:users,phone_number',
            'code' => 'required',
        ]);
        $phone_number = $this->convertor($request->input('phone_number'));
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $code = Code::where('code', $request->input('code'))->where('phone_number', $phone_number)->firstOr(function(){
            return response()->json([
                'data' => ['code' => ['???? ???????? ?????? ?????????????? ????????????']],
                'status' => 'error',
            ]);
        });
        $user = User::where('phone_number', $phone_number)->first();
        $status = Code::ValidateCode($request->input('code'));
        if ($status) {
            return response()->json([
                'data' => ['token' => $user->createToken('API Token')->plainTextToken],
                'status' => 'success'
            ]);
        }
        return response()->json([
            'data' => ['code' => ['???? ?? ???? ?????????? ???????? ???????? ?????? ?????????????? ????????????']],
            'status' => 'error',
        ]);
    }

    public function sendSms($phone_number)
    {
        $notif = new SmsProvider($phone_number);
        $notif->send();
        // SendSmsCode::dispatch($phone_number);
    }
    public function register(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone_number' => 'required|unique:users,phone_number',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        $phone_number = $this->convertor($request->input('phone_number'));
        $user = User::create([
            'name' => $request->input('name'),
            'phone_number' => $phone_number,
        ]);
        $this->sendSms($phone_number);
        return response()->json([
            'data' => [
                'action' => 'login',
                'phone_number' => $phone_number,
            ],
            'status' => 'success',
        ]);
    }
    private function convertor($phone_number)
    {
        return convertEnglishToPersian::convertPersianToEnglish($phone_number);

    }
}
