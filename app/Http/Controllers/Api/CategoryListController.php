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
            $categories->getCollection()->transform([$this, 'addImagePaths']);
        } else {
            $categories = $query->get();
            $categories->transform([$this, 'addImagePaths']);
        }
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    public function addImagePaths($category)
    {
        $baseUrl = 'https://medicalsupplierz.app/storage/image/';
        $timestamp = '?v=' . strtotime($category->updated_at);
        
        $category->image_path = $category->image ? $baseUrl . $category->image . $timestamp : null;
        $category->cover_image_path = $category->cover_image ? $baseUrl . $category->cover_image . $timestamp : null;
        $category->icon_image_path = $category->icon_image ? $baseUrl . $category->icon_image . $timestamp : null;
        
        return $category;
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