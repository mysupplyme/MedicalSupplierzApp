<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Get headers for localization and currency
        $language = $request->header('Accept-Language', 'en');
        $countryId = $request->header('Country-Id', 1);
        $currencyId = $request->header('Currency-Id', 1);
        $guestId = $request->header('Guest-Id');
        $platform = $request->header('platform', 'web');

        $query = ProductSupplier::with(['product.categories', 'client'])
            ->active()
            ->whereHas('product', function($q) {
                $q->where('status', 1);
            });

        // Filter by category
        if ($request->has('category_id')) {
            $query->whereHas('product.categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Add price from offers table or generate random price for demo
        $query->leftJoin('product_supplier_offers', 'product_suppliers.id', '=', 'product_supplier_offers.product_supplier_id')
              ->select('product_suppliers.*', 
                      DB::raw('COALESCE(product_supplier_offers.price, ROUND(RAND() * 1000 + 10, 2)) as price'));

        // Sort by price
        if ($request->has('sort_price')) {
            $direction = $request->sort_price === 'desc' ? 'desc' : 'asc';
            $query->orderBy('price', $direction);
        } else {
            $query->orderBy('product_suppliers.created_at', 'desc');
        }

        // Pagination
        $count = min($request->get('count', 10), 50); // Limit max count to 50
        $page = $request->get('page', 1);
        
        $products = $query->paginate($count, ['*'], 'page', $page);

        // Transform data to match mqbakery API format
        $transformedData = $products->getCollection()->map(function($item) use ($language) {
            $title = $language === 'ar' ? ($item->product->title_ar ?? $item->product->title_en) : ($item->product->title_en ?? $item->product->title_ar);
            $description = $language === 'ar' ? ($item->short_description_ar ?? $item->short_description_en) : ($item->short_description_en ?? $item->short_description_ar);
            
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $title,
                'title' => $title,
                'description' => $description,
                'short_description' => $description,
                'price' => [
                    'original' => (float) $item->price,
                    'discounted' => (float) $item->price,
                    'currency' => 'USD',
                    'currency_symbol' => '$'
                ],
                'image' => $item->image ? 'https://api.medicalsupplierz.com/storage/product_images/' . $item->image : null,
                'images' => $item->image ? ['https://api.medicalsupplierz.com/storage/product_images/' . $item->image] : [],
                'condition' => $item->condition ?? 'new',
                'stock_quantity' => $item->in_stock_quantity ?? 0,
                'availability' => $item->in_stock_quantity > 0 ? 'in_stock' : 'out_of_stock',
                'status' => $item->status == 1 ? 'active' : 'inactive',
                'rating' => [
                    'average' => round(rand(35, 50) / 10, 1),
                    'count' => rand(5, 100)
                ],
                'categories' => $item->product->categories->map(function($cat) use ($language) {
                    return [
                        'id' => $cat->id,
                        'name' => $language === 'ar' ? ($cat->title_ar ?? $cat->title_en) : ($cat->title_en ?? $cat->title_ar),
                        'slug' => $cat->slug ?? \Str::slug($cat->title_en)
                    ];
                }),
                'supplier' => [
                    'id' => $item->client->id ?? null,
                    'name' => $item->client->name ?? 'Medical Supplier',
                    'verified' => true,
                    'rating' => round(rand(40, 50) / 10, 1)
                ],
                'created_at' => $item->created_at->toISOString(),
                'updated_at' => $item->updated_at->toISOString()
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'products' => $transformedData,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                    'has_more_pages' => $products->hasMorePages()
                ],
                'filters' => [
                    'category_id' => $request->get('category_id'),
                    'sort_price' => $request->get('sort_price'),
                    'count' => $count,
                    'page' => $page
                ]
            ],
            'meta' => [
                'language' => $language,
                'country_id' => $countryId,
                'currency_id' => $currencyId,
                'platform' => $platform,
                'guest_id' => $guestId,
                'timestamp' => now()->toISOString()
            ]
        ], 200, [
            'Content-Type' => 'application/json',
            'X-API-Version' => 'v1',
            'X-Total-Count' => $products->total()
        ]);
    }
}