<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ProductCollection;
use App\Http\Resources\v1\torobPrdocutsCollection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

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
        return new ProductCollection($products);
    }
    public function newest()
    {
        $products = Product::limit(10)->orderBy('created_at', 'desc')->get();
        return new ProductCollection($products);
    }
    public function mostVisited()
    {
        $products = Product::limit(10)->orderBy('views', 'desc')->get();
        return new ProductCollection($products);
    }
    public function sitemap()
    {
        $products = Product::all();
        return view('sitemap' , compact('products'));
    }
    public function torob(Request $request)
    {
        $products = Product::all();
        $fulls = [];
        foreach ($products as $product) {
            $fullstmp = $product->fulls->sortBy('price');
            array_push($fulls, $fullstmp->first());
        }
        $fulls = Arr::flatten($fulls);
        // dd($fulls[0]->product);
        $count = count($fulls);
        $fulls = new LengthAwarePaginator($fulls, $count, 100);
        return response()->json([
            'count' => $count,
            new torobPrdocutsCollection($fulls)
        ]);
    }
}
