<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\CommentCollection;
use App\Http\Resources\v1\CommentsForAdminCollection;
use App\Http\Resources\v1\CommentsForUserCollection;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommntController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->except(['ByProduct']);
        $this->middleware(['role:admin'])->only(['index' , 'delete' , 'changeStatus']);

    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'desc' => 'required|min:2|max:500',
            'score' => 'required|integer|min:1|max:5',
            'product_id' => 'required|integer|exists:products,id',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $comment = auth()->user()->comments()->create([
            'desc' => $request->input('desc'),
            'score' => $request->input('score'),
            'product_id' => $request->input('product_id'),
            'is_active' => 0,
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function index()
    {
        $comments = Comment::paginate(10);
        return new CommentsForAdminCollection($comments);
    }

    public function delete(Comment $comment)
    {
        $comment->delete();
        return response()->json([
            'data' => [],
            'status' => 'success',
        ],200);
    }
    public function changeStatus(Request $request , Comment $comment)
    {
        $validator = Validator::make($request->all(), [
            'is_active' => 'required|integer|min:0|max:1',
          ]);
          if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'status' => 'error',
            ]);
          }
        $comment->update([
            'is_active' => $request->input('is_active'),
        ]);
        return response()->json([
            'data' => [],
            'status' => 'success',
        ]);
    }
    public function ByProduct(Product $product)
    {
        $comments = $product->comments()->paginate(10);
        return new CommentCollection($comments); 
    }
    public function ByUser()
    {
        return new CommentsForUserCollection(auth()->user()->comments); 
    }
}
