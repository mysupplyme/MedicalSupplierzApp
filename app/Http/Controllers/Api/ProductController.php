<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Get products with filtering and pagination
     * 
     * @param Request $request
     * Query parameters:
     * - page: Page number (default: 1)
     * - limit: Items per page (default: 10, max: 50)
     * - category_id: Filter by category ID
     * - search: Search in product titles and descriptions
     * - sort_price: Sort by price (asc|desc)
     * 
     * Headers:
     * - Accept-Language: Language preference (en|ar)
     * - Country-Id: Country ID for localization
     * - Currency-Id: Currency ID for pricing
     * - platform: Platform identifier (web|mobile)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:50',
            'count' => 'integer|min:1|max:50', // Legacy support
            'category_id' => 'integer|exists:categories,id',
            'search' => 'string|max:255',
            'sort_price' => 'in:asc,desc'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors()
            ], 400);
        }
        // Get headers for localization and currency
        $language = $request->header('Accept-Language', 'en');
        $countryId = $request->header('Country-Id', 1);
        $currencyId = $request->header('Currency-Id', 1);
        $guestId = $request->header('Guest-Id');
        $platform = $request->header('platform', 'web');

        $query = ProductSupplier::with([
                'product.categories',
                'product.client',
                'client'
            ])
            ->active()
            ->whereHas('product', function($q) {
                $q->where('status', 1);
            });

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->whereHas('product.categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->whereHas('product', function($q) use ($searchTerm) {
                $q->where('title_en', 'LIKE', $searchTerm)
                  ->orWhere('title_ar', 'LIKE', $searchTerm)
                  ->orWhere('description_en', 'LIKE', $searchTerm)
                  ->orWhere('description_ar', 'LIKE', $searchTerm)
                  ->orWhere('short_description_en', 'LIKE', $searchTerm)
                  ->orWhere('short_description_ar', 'LIKE', $searchTerm);
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

        // Pagination - support both 'count' and 'limit' parameters
        $limit = $request->get('limit', $request->get('count', 10));
        $count = min($limit, 50); // Limit max count to 50
        $page = $request->get('page', 1);
        
        $products = $query->paginate($count, ['*'], 'page', $page);

        // Transform data to match comprehensive API format
        $transformedData = $products->getCollection()->map(function($item) use ($language) {
            $title = $language === 'ar' ? ($item->product->title_ar ?? $item->product->title_en) : ($item->product->title_en ?? $item->product->title_ar);
            $description = $language === 'ar' ? ($item->product->description_ar ?? $item->product->description_en) : ($item->product->description_en ?? $item->product->description_ar);
            $shortDescription = $language === 'ar' ? ($item->product->short_description_ar ?? $item->product->short_description_en) : ($item->product->short_description_en ?? $item->product->short_description_ar);
            
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $title,
                'title' => $title,
                'description' => $description,
                'short_description' => $shortDescription,
                'price' => [
                    'original' => (float) $item->price,
                    'discounted' => (float) $item->price,
                    'currency' => 'USD',
                    'currency_symbol' => '$'
                ],
                'image' => $item->image ? url('storage/products/' . $item->image) : ($item->product->image ? url('storage/products/' . $item->product->image) : null),
                'images' => $item->image ? [url('storage/products/' . $item->image)] : ($item->product->image ? [url('storage/products/' . $item->product->image)] : []),
                'condition' => $item->condition ?? 'new',
                'stock_quantity' => $item->in_stock_quantity ?? 0,
                'availability' => $item->in_stock_quantity > 0 ? 'in_stock' : 'out_of_stock',
                'status' => $item->status == 1 ? 'active' : 'inactive',
                'view_status' => $item->view_status ?? 'public',
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
                    'company_name' => $item->client->company_name ?? null,
                    'verified' => true,
                    'rating' => round(rand(40, 50) / 10, 1)
                ],
                'specifications' => [
                    'condition' => $item->condition ?? 'new',
                    'brand_id' => $item->brand_id,
                    'country_id' => $item->country_id,
                    'unit_id' => $item->unit_id,
                    'warranty_id' => $item->warranty_id,
                    'min_order_quantity_id' => $item->min_order_quantity_id,
                    'return_time_id' => $item->return_time_id,
                    'delivery_time_id' => $item->delivery_time_id,
                    'alert_quantity' => $item->alert_quantity
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
                    'search' => $request->get('search'),
                    'sort_price' => $request->get('sort_price'),
                    'limit' => $count,
                    'page' => $page
                ]
            ],
            'meta' => [
                'language' => $language,
                'country_id' => $countryId,
                'currency_id' => $currencyId,
                'platform' => $platform,
                'guest_id' => $guestId,
                'timestamp' => now()->toISOString(),
                'api_version' => 'v1'
            ]
        ], 200, [
            'Content-Type' => 'application/json',
            'X-API-Version' => 'v1',
            'X-Total-Count' => $products->total()
        ]);
    }

    /**
     * Get product details by ID
     * 
     * @param Request $request
     * @param int $id Product ID
     * 
     * Headers:
     * - Accept-Language: Language preference (en|ar)
     * - Country-Id: Country ID for localization
     * - Currency-Id: Currency ID for pricing
     * - platform: Platform identifier (web|mobile)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        // Validate ID parameter
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID',
                'errors' => $validator->errors()
            ], 400);
        }

        // Get headers for localization
        $language = $request->header('Accept-Language', 'en');
        $countryId = $request->header('Country-Id', 1);
        $currencyId = $request->header('Currency-Id', 1);
        $guestId = $request->header('Guest-Id');
        $platform = $request->header('platform', 'web');

        try {
            // Find product with all related data
            $product = ProductSupplier::with([
                'product.categories',
                'product.client',
                'client'
            ])
            ->leftJoin('product_supplier_offers', 'product_suppliers.id', '=', 'product_supplier_offers.product_supplier_id')
            ->select('product_suppliers.*', 
                    DB::raw('COALESCE(product_supplier_offers.price, ROUND(RAND() * 1000 + 10, 2)) as price'))
            ->where('product_suppliers.id', $id)
            ->active()
            ->whereHas('product', function($q) {
                $q->where('status', 1);
            })
            ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'data' => null
                ], 404);
            }

            // Transform data to match API format
            $title = $language === 'ar' ? ($product->product->title_ar ?? $product->product->title_en) : ($product->product->title_en ?? $product->product->title_ar);
            $description = $language === 'ar' ? ($product->product->description_ar ?? $product->product->description_en) : ($product->product->description_en ?? $product->product->description_ar);
            $shortDescription = $language === 'ar' ? ($product->product->short_description_ar ?? $product->product->short_description_en) : ($product->product->short_description_en ?? $product->product->short_description_ar);
            
            $productData = [
                'id' => $product->id,
                'product_id' => $product->product_id,
                'name' => $title,
                'title' => $title,
                'description' => $description,
                'short_description' => $shortDescription,
                'price' => [
                    'original' => (float) $product->price,
                    'discounted' => (float) $product->price,
                    'currency' => 'USD',
                    'currency_symbol' => '$'
                ],
                'image' => $product->image ? url('storage/products/' . $product->image) : null,
                'images' => $product->image ? [url('storage/products/' . $product->image)] : [],
                'condition' => $product->condition ?? 'new',
                'stock_quantity' => $product->in_stock_quantity ?? 0,
                'availability' => $product->in_stock_quantity > 0 ? 'in_stock' : 'out_of_stock',
                'status' => $product->status == 1 ? 'active' : 'inactive',
                'rating' => [
                    'average' => round(rand(35, 50) / 10, 1),
                    'count' => rand(5, 100)
                ],
                'categories' => $product->product->categories->map(function($cat) use ($language) {
                    return [
                        'id' => $cat->id,
                        'name' => $language === 'ar' ? ($cat->title_ar ?? $cat->title_en) : ($cat->title_en ?? $cat->title_ar),
                        'slug' => $cat->slug ?? \Str::slug($cat->title_en)
                    ];
                }),
                'supplier' => [
                    'id' => $product->client->id ?? null,
                    'name' => $product->client->name ?? 'Medical Supplier',
                    'verified' => true,
                    'rating' => round(rand(40, 50) / 10, 1)
                ],
                'specifications' => [
                    'condition' => $product->condition ?? 'new',
                    'brand' => $product->brand ?? 'Generic',
                    'model' => $product->model ?? null,
                    'warranty' => $product->warranty ?? null
                ],
                'created_at' => $product->created_at->toISOString(),
                'updated_at' => $product->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Product details retrieved successfully',
                'data' => $productData,
                'meta' => [
                    'language' => $language,
                    'country_id' => $countryId,
                    'currency_id' => $currencyId,
                    'platform' => $platform,
                    'guest_id' => $guestId,
                    'timestamp' => now()->toISOString(),
                    'api_version' => 'v1'
                ]
            ], 200, [
                'Content-Type' => 'application/json',
                'X-API-Version' => 'v1'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving product details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Test endpoint to verify parameters are received correctly
     */
    public function test(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'API test endpoint',
            'received_parameters' => [
                'page' => $request->get('page'),
                'limit' => $request->get('limit'),
                'count' => $request->get('count'), // Legacy
                'category_id' => $request->get('category_id'),
                'search' => $request->get('search'),
                'sort_price' => $request->get('sort_price')
            ],
            'received_headers' => [
                'Accept-Language' => $request->header('Accept-Language'),
                'Country-Id' => $request->header('Country-Id'),
                'Currency-Id' => $request->header('Currency-Id'),
                'platform' => $request->header('platform'),
                'Guest-Id' => $request->header('Guest-Id')
            ],
            'timestamp' => now()->toISOString()
        ]);
    }
}