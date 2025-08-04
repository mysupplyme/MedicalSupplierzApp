<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryListController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();
        
        // Filter by parent_id
        if ($request->has('parent_id')) {
            $query->where('category_id', $request->parent_id);
        } else {
            // Get only conferences and expos
            $query->whereIn('id', [3021, 3022]);
        }
        
        // Search by keyword
        if ($request->has('keyword')) {
            $query->where('title_en', 'like', '%' . $request->keyword . '%');
        }
        
        // Pagination
        if ($request->boolean('pagination')) {
            $count = $request->get('count', 10);
            $categories = $query->paginate($count);
        } else {
            $categories = $query->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    public function tree()
    {
        $categories = Category::whereIn('id', [3021, 3022])
            ->with(['children.children'])
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}