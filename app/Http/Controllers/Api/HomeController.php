<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ProductCollection;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function search(Request $request)
    {
        // dd($request->all());
        $query = $request->input('query');
        if (!$query)
            return response()->json([
                'data' => [],
                'status' => 'error',
            ]);
        $products = Product::where('persian_title', 'LIKE', '%' . $query . '%')
            ->orWhere('persian_title', 'LIKE', '%' . $query)
            ->orWhere('persian_title', 'LIKE', $query . '%')
            ->orWhere('title', 'LIKE', '%' . $query . '%')
            ->orWhere('title', 'LIKE', $query . '%')
            ->orWhere('title', 'LIKE', '%' . $query)->paginate(10);
            // dd($products[0]->id);
        return response()->json([
            'data' => new ProductCollection($products),
            'status' => 'error',
        ]); // TODO make a pagination for here
    }
}
