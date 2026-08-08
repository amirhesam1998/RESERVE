<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Throwable;

class CategoryController extends Controller
{
    public function tree()
    {
        try {
            $categories = Category::whereNull('parent_id')->with('children')->get();

            return response()->json($categories);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'somthing went wrong, try again later'
            ], 500);
        }
    }

    public function children(Category $category)
    {
        try {
            return  response()->json($category->children);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }
}
