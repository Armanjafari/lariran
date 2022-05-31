<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Notifications\Notification;
use Illuminate\Http\Request;

class AuthWithCodeController extends Controller
{
    public $code;
    public function __construct()
    {
        $this->middleware('guest');
        // $this->middleware('auth:sanctum')->only(['me', 'logout']);
        $this->middleware('throttle:5,5')->only(['register']); // TODO set captcha for everywhere that needed
        $this->middleware('throttle:5,5')->only(['login']);
    }
    public function login(Request $request)
    {

        $phone_number = $request->input('phone_number') ?? session()->get('phone_number');
        session()->put('phone_number', $phone_number);
        $user = User::where('phone_number',$phone_number)->first();
        if (!$user) {
                return redirect()->route('register.form.with.code');
        }

        $this->sendSms($user , 'code');
        return redirect()->route('verify_login_code');
    }
    public function registerForm()
    {
        $provinces = Province::all();
        return view('AuthWithCode.register',compact('provinces'));
    }
    public function sendSms($user, $method)
    {
        $notif = resolve(Notification::class);
        $notif->SmsProvider()->send();
    }
    public function verifyForm()
    {
        return view('AuthWithCode.verify');
    }
    public function username()
    {
        return "phone_number";
    }
    public function codeValidator(CodeValidator $request)
    {
        $user = User::where('phone_number',session()->get('phone_number'))->first();
        session()->forget('phone_number');
        $status = ActiveCode::ValidateCode($request->input('code'),$user);
        //dd($status);
        if( $status)
        {
            Auth::login($user);
            return redirect()->route('index');
        }
        return redirect()->back()->withErrors('errors', 'کد منقظی شده است');   
    }
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'city' => 'required',
            'postal_code' => 'required',
            'address' => 'required',
        ]);
        $user = User::create([
            'name' => $request->input('name'),
            'phone_number' => session()->get('phone_number'),
        ]);
        $user->shipings()->create([
            'city_id' => $request->input('city'),
            'postal_code' => $request->input('postal_code'),
            'address' => $request->input('address'),
        ]);
        $this->sendSms($user , 'code');
        return redirect()->route('verify_login_code')->withSuccess(' ثبت نام با موفقیت انجام شد ');
        
    }
}
