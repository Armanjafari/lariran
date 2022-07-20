<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ProductCollection;
use App\Http\Resources\v1\torobPrdocutsCollection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

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
        return view('sitemap', compact('products'));
    }
    public function torob(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_unique' => 'integer|exists:products,id',
            'page_url' => 'url',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
        }
        if ($request->has('page_unique')) {
            return $this->torobSingle($request);
        }else if($request->has('page_url')){
            return $this->torobPageUrl($request);

        }
        $products = Product::orderBy('created_at', 'desc')->get();
        $fulls = [];
        foreach ($products as $product) {
            $fullstmp = $product->fulls->sortBy('price');
            foreach ($fullstmp as $full) {
                $last = $fullstmp->last();
                if ($full == $last) {
                    array_push($fulls, $full);
                    break;
                }
                if ((int)$full->stock) {
                    array_push($fulls, $full);
                    break;
                }
            }
        }
        $fulls = Arr::flatten($fulls);
        // dd($fulls[0]->product);
        $count = count($fulls);
        $fulls = new LengthAwarePaginator($fulls, $count, 100);
        return response()->json([
            'count' => $count,
            'max_pages' => $fulls->lastPage(),
            'products' => new torobPrdocutsCollection($fulls),
        ]);
    }
    private function torobSingle(Request $request)
    {
        $product = Product::with('fulls')->Where('id',  $request->page_unique)->first();
        $fullstmp = $product->fulls->sortBy('price');
        $fulls = [];
        foreach ($fullstmp as $full) {
            $last = $fullstmp->last();
            if ($full == $last) {
                array_push($fulls, $full);
                break;
            }
            if ((int)$full->stock) {
                array_push($fulls, $full);
                break;
            }
        }
        return response()->json([
            'count' => 1,
            'max_pages' => 1,
            'products' => new torobPrdocutsCollection($fulls),
        ]);
    }
    private function torobPageUrl(Request $request)
    {
        $url = parse_url($request->page_url);

        $seprated = explode('/',$url['path']);
        $id = $seprated[2];
        if ($url['scheme'] != 'https' || $url['host'] != 'lariran.com' || !is_numeric($id)) {
            return response()->json([
                'data' => 'فرمت آدرس اشتباه میباشد',
                'status' => 'error',
            ]);
        }
        $product = Product::with('fulls')->findOrFail($id);
        $fullstmp = $product->fulls->sortBy('price');
        $fulls = [];
        foreach ($fullstmp as $full) {
            $last = $fullstmp->last();
            if ($full == $last) {
                array_push($fulls, $full);
                break;
            }
            if ((int)$full->stock) {
                array_push($fulls, $full);
                break;
            }
        }
        return response()->json([
            'count' => 1,
            'max_pages' => 1,
            'products' => new torobPrdocutsCollection($fulls),
        ]);

    }
}
