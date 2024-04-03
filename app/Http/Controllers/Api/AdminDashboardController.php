<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $data = [];
        $data['orders'] = Payment::where('status' , '>' , 1)->except('status' , '=' , 100)->get();
        // $data['orderCount'] = Payment::
        dd($data);
    }
}
