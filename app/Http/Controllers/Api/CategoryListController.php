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
        
        $query->select('id', 'title_en as name', 'description_en as description', 'image', 'cover_image', 'icon_image', 'updated_at');
        
        // Pagination
        if ($request->boolean('pagination')) {
            $count = $request->get('count', 10);
            $categories = $query->paginate($count);
            $transformedData = $categories->getCollection()->map([$this, 'transformCategory']);
            $categories->setCollection($transformedData);
        } else {
            $categories = $query->get()->map([$this, 'transformCategory']);
        }
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ])->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
          ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
    
    private function transformCategory($category)
    {
        $baseUrl = 'https://medicalsupplierz.app/storage/image/';
        $timestamp = '?v=' . strtotime($category->updated_at);
        
        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'image_path' => $category->image ? $baseUrl . $category->image . $timestamp : null,
            'cover_image_path' => $category->cover_image ? $baseUrl . $category->cover_image . $timestamp : null,
            'icon_image_path' => $category->icon_image ? $baseUrl . $category->icon_image . $timestamp : null
        ];
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