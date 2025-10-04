<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryProduct;
use App\Models\ProductSupplier;
use App\Models\ProductSupplierB2b;
use App\Models\Client;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getCategories()
    {
        $categories = Category::whereIn('id', [3021, 3022])
            ->select('id', 'title_en as name', 'description_en as description', 'image', 'cover_image', 'icon_image', 'updated_at')
            ->get()
            ->map(function($category) {
                $baseUrl = 'https://medicalsupplierz.app/storage/image/';
                $timestamp = '?v=' . strtotime($category->updated_at);
                
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'image_path' => $category->image ? $baseUrl . $category->image . '.webp' . $timestamp : null,
                    'cover_image_path' => $category->cover_image ? $baseUrl . $category->cover_image . '.webp' . $timestamp : null,
                    'icon_image_path' => $category->icon_image ? $baseUrl . $category->icon_image . '.webp' . $timestamp : null
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    public function getSpecialties($categoryId = null)
    {
        $query = Category::where('category_id', $categoryId ?: 3021); // Default to conference id
        $specialties = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $specialties
        ]);
    }
    
    public function getSubSpecialties(Request $request)
    {
        $specialtyId = $request->specialty_id;
        $subSpecialties = Category::where('category_id', $specialtyId)->get();
        
        return response()->json([
            'success' => true,
            'data' => $subSpecialties
        ]);
    }
    
    public function getCategoryProducts($categoryId)
    {
        $products = CategoryProduct::where('category_id', $categoryId)->get();
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
    
    public function getProductSuppliers($productId)
    {
        $suppliers = ProductSupplier::where('product_id', $productId)->get();
        
        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }
    
    public function getClients()
    {
        $clients = Client::where('buyer_type', 'doctor')->get();
        
        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    }
    
    public function getDoctors()
    {
        $doctors = Client::where('buyer_type', 'doctor')->get();
        
        return response()->json([
            'success' => true,
            'data' => $doctors
        ]);
    }
    
    public function getConferences()
    {
        $conferences = ProductSupplierB2b::with('category')->get();
        
        return response()->json([
            'success' => true,
            'data' => $conferences
        ]);
    }
}