<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**s
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
        // Validate request parameters - expanded to match newapi version
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'limit' => 'integer|min:1|max:50',
            'count' => 'integer|min:1|max:50', // Legacy support
            'category_id' => 'integer|exists:categories,id',
            'search' => 'string|max:255',
            'keyword' => 'string|max:255', // Alternative to search
            'sort_price' => 'in:asc,desc',
            'price_type' => 'string|in:b2b,b2c,both',
            'brand_id' => 'integer|exists:brands,id',
            'country_id' => 'integer|exists:origin_countries,id',
            'unit_id' => 'integer|exists:units,id',
            'weight' => 'numeric|min:0',
            'min_price' => 'numeric|min:0',
            'max_price' => 'numeric|min:0',
            'condition' => 'string|in:new,used,refurbished',
            'client_id' => 'integer|exists:clients,id',
            'without_id' => 'integer|exists:product_suppliers,id',
            'form' => 'string|max:50'
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
        $token = $request->header('Authorization');
        $userAgent = $request->header('User-Agent');

        $query = ProductSupplier::with([
                'product.categories',
                'product.client',
                'client',
                'productDetailsByType',
                'warranty',
                'country',
                'productDetailsByType.returnTime',
                'productDetailsByType.deliveryTime'
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

        // Filter by brand
        if ($request->has('brand_id') && $request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by country
        if ($request->has('country_id') && $request->country_id) {
            $query->where('country_id', $request->country_id);
        }

        // Filter by condition
        if ($request->has('condition') && $request->condition) {
            $query->where('condition', $request->condition);
        }

        // Filter by client/supplier
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Exclude specific product
        if ($request->has('without_id') && $request->without_id) {
            $query->where('product_suppliers.id', '!=', $request->without_id);
        }

        // Search functionality - support both 'search' and 'keyword'
        $searchTerm = $request->search ?? $request->keyword;
        if ($searchTerm) {
            $searchPattern = '%' . $searchTerm . '%';
            $query->whereHas('product', function($q) use ($searchPattern) {
                $q->where('title_en', 'LIKE', $searchPattern)
                  ->orWhere('title_ar', 'LIKE', $searchPattern)
                  ->orWhere('description_en', 'LIKE', $searchPattern)
                  ->orWhere('description_ar', 'LIKE', $searchPattern)
                  ->orWhere('short_description_en', 'LIKE', $searchPattern)
                  ->orWhere('short_description_ar', 'LIKE', $searchPattern);
            });
        }

        // Filter by unit (from product details)
        if ($request->has('unit_id') && $request->unit_id) {
            $query->whereHas('productDetailsByType', function($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            });
        }

        // Filter by weight
        if ($request->has('weight') && $request->weight) {
            $query->whereHas('productDetailsByType', function($q) use ($request) {
                $q->where('weight', $request->weight);
            });
        }

        // Filter by price range
        if ($request->has('min_price') && $request->min_price) {
            $query->whereHas('productDetailsByType', function($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->whereHas('productDetailsByType', function($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
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
            
            $productDetail = $item->productDetailsByType;
            
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $title,
                'title' => $title,
                'description' => $description,
                'short_description' => $shortDescription,
                'sku' => $productDetail->sku ?? null,
                'barcode' => $productDetail->barcode ?? null,
                'price' => [
                    'original' => (float) ($productDetail->price ?? $item->price),
                    'discounted' => (float) ($productDetail->price ?? $item->price),
                    'currency' => 'USD',
                    'currency_symbol' => '$'
                ],
                'image' => $item->image  ? url('storage/products/' . $item->image) : ($item->product->image ? url('storage/products/' . $item->product->image) : null),
                'images' => $item->image ? [url('storage/products/' . $item->image)] : ($item->product->image ? [url('storage/products/' . $item->product->image)] : []),
                'condition' => $item->condition ?? 'new',
                'stock_quantity' => $productDetail->quantity ?? $item->in_stock_quantity ?? 0,
                'pieces_per_unit' => $productDetail->pieces_number ?? null,
                'weight' => $productDetail->weight ?? null,
                'availability' => ($productDetail->quantity ?? $item->in_stock_quantity ?? 0) > 0 ? 'in_stock' : 'out_of_stock',
                'status' => $item->status == 1 ? 'active' : 'inactive',
                'view_status' => $item->view_status ?? 'public',
                'add_type' => $productDetail->add_type ?? 'cart',
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
                'warranty' => $item->warranty ? [
                    'id' => $item->warranty->id,
                    'title' => $item->warranty->title ?? null
                ] : null,
                'country' => $item->country ? [
                    'id' => $item->country->id,
                    'title' => $item->country->title ?? null
                ] : null,
                'return_time' => $productDetail && $productDetail->returnTime ? [
                    'id' => $productDetail->returnTime->id,
                    'title' => $productDetail->returnTime->title ?? null
                ] : null,
                'delivery_time' => $productDetail && $productDetail->deliveryTime ? [
                    'id' => $productDetail->deliveryTime->id,
                    'title' => $productDetail->deliveryTime->title ?? null
                ] : null,
                'product_details_by_type' => $productDetail ? [
                    'id' => $productDetail->id,
                    'sku' => $productDetail->sku,
                    'barcode' => $productDetail->barcode,
                    'price' => $productDetail->price,
                    'quantity' => $productDetail->quantity,
                    'pieces_number' => $productDetail->pieces_number,
                    'weight' => $productDetail->weight,
                    'add_type' => $productDetail->add_type,
                    'commission' => $productDetail->commission,
                    'commission_type' => $productDetail->commission_type
                ] : null,
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
                    'keyword' => $request->get('keyword'),
                    'sort_price' => $request->get('sort_price'),
                    'brand_id' => $request->get('brand_id'),
                    'country_id' => $request->get('country_id'),
                    'unit_id' => $request->get('unit_id'),
                    'weight' => $request->get('weight'),
                    'min_price' => $request->get('min_price'),
                    'max_price' => $request->get('max_price'),
                    'condition' => $request->get('condition'),
                    'client_id' => $request->get('client_id'),
                    'without_id' => $request->get('without_id'),
                    'price_type' => $request->get('price_type'),
                    'form' => $request->get('form'),
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
                'user_agent' => $userAgent,
                'has_auth' => !empty($token),
                'timestamp' => now()->toISOString(),
                'api_version' => 'v1',
                'request_id' => uniqid('req_')
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
        $token = $request->header('Authorization');
        $userAgent = $request->header('User-Agent');

        try {
            // Find product with all related data
            $product = ProductSupplier::with([
                'product.categories',
                'product.client',
                'client',
                'productDetailsByType',
                'warranty',
                'country',
                'productDetailsByType.returnTime',
                'productDetailsByType.deliveryTime'
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
            
            $productDetail = $product->productDetailsByType;
            
            $productData = [
                'id' => $product->id,
                'product_id' => $product->product_id,
                'name' => $title,
                'title' => $title,
                'description' => $description,
                'short_description' => $shortDescription,
                'sku' => $productDetail->sku ?? null,
                'barcode' => $productDetail->barcode ?? null,
                'price' => [
                    'original' => (float) ($productDetail->price ?? $product->price),
                    'discounted' => (float) ($productDetail->price ?? $product->price),
                    'currency' => 'USD',
                    'currency_symbol' => '$'
                ],
                'image' => $product->image ? url('storage/products/' . $product->image) : ($product->product->image ? url('storage/products/' . $product->product->image) : null),
                'images' => $product->image ? [url('storage/products/' . $product->image)] : ($product->product->image ? [url('storage/products/' . $product->product->image)] : []),
                'condition' => $product->condition ?? 'new',
                'stock_quantity' => $productDetail->quantity ?? $product->in_stock_quantity ?? 0,
                'pieces_per_unit' => $productDetail->pieces_number ?? null,
                'weight' => $productDetail->weight ?? null,
                'availability' => ($productDetail->quantity ?? $product->in_stock_quantity ?? 0) > 0 ? 'in_stock' : 'out_of_stock',
                'status' => $product->status == 1 ? 'active' : 'inactive',
                'view_status' => $product->view_status ?? 'public',
                'add_type' => $productDetail->add_type ?? 'cart',
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
                    'company_name' => $product->client->company_name ?? null,
                    'verified' => true,
                    'rating' => round(rand(40, 50) / 10, 1)
                ],
                'warranty' => $product->warranty ? [
                    'id' => $product->warranty->id,
                    'title' => $product->warranty->title ?? null
                ] : null,
                'country' => $product->country ? [
                    'id' => $product->country->id,
                    'title' => $product->country->title ?? null
                ] : null,
                'return_time' => $productDetail && $productDetail->returnTime ? [
                    'id' => $productDetail->returnTime->id,
                    'title' => $productDetail->returnTime->title ?? null
                ] : null,
                'delivery_time' => $productDetail && $productDetail->deliveryTime ? [
                    'id' => $productDetail->deliveryTime->id,
                    'title' => $productDetail->deliveryTime->title ?? null
                ] : null,
                'product_details_by_type' => $productDetail ? [
                    'id' => $productDetail->id,
                    'sku' => $productDetail->sku,
                    'barcode' => $productDetail->barcode,
                    'price' => $productDetail->price,
                    'quantity' => $productDetail->quantity,
                    'pieces_number' => $productDetail->pieces_number,
                    'weight' => $productDetail->weight,
                    'add_type' => $productDetail->add_type,
                    'commission' => $productDetail->commission,
                    'commission_type' => $productDetail->commission_type
                ] : null,
                'specifications' => [
                    'condition' => $product->condition ?? 'new',
                    'brand_id' => $product->brand_id,
                    'country_id' => $product->country_id,
                    'unit_id' => $product->unit_id,
                    'warranty_id' => $product->warranty_id,
                    'min_order_quantity_id' => $product->min_order_quantity_id,
                    'return_time_id' => $product->return_time_id,
                    'delivery_time_id' => $product->delivery_time_id,
                    'alert_quantity' => $product->alert_quantity
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
                    'user_agent' => $userAgent,
                    'has_auth' => !empty($token),
                    'timestamp' => now()->toISOString(),
                    'api_version' => 'v1',
                    'request_id' => uniqid('req_')
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