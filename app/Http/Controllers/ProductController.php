<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin'])->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Handle AJAX requests from orders page (simple JSON format)
        if ($request->ajax() || $request->wantsJson()) {
            // Check if this is a DataTables request (has 'draw' parameter)
            if ($request->has('draw')) {
                // Handle DataTables server-side processing
                $query = Product::with(['category']);

                // Apply custom filters
                if ($request->has('search') && !empty($request->search)) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('sku', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }

                if ($request->has('category_id') && !empty($request->category_id)) {
                    $query->where('category_id', $request->category_id);
                }

                if ($request->has('status') && !empty($request->status)) {
                    $status = $request->status;
                    if ($status === 'active') {
                        $query->where('is_active', true);
                    } elseif ($status === 'inactive') {
                        $query->where('is_active', false);
                    } elseif ($status === 'low_stock') {
                        $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
                    }
                }

                // Handle DataTables parameters
                $totalRecords = $query->count();

                // Apply ordering
                if ($request->has('order') && isset($request->order[0])) {
                    $orderColumn = $request->order[0]['column'];
                    $orderDir = $request->order[0]['dir'];

                    $columns = ['id', 'name', 'sku', 'category_id', 'price', 'stock_quantity', 'is_active', 'created_at'];
                    if (isset($columns[$orderColumn])) {
                        $query->orderBy($columns[$orderColumn], $orderDir);
                    }
                } else {
                    $query->orderBy('created_at', 'desc');
                }

                // Apply pagination
                $start = $request->get('start', 0);
                $length = $request->get('length', 15);
                $products = $query->skip($start)->take($length)->get();

                return response()->json([
                    'draw' => intval($request->get('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $products
                ]);
            } else {
                // Handle simple AJAX requests (from orders page)
                $query = Product::with(['category'])
                    ->active()
                    ->inStock();

                // Apply search filter
                if ($request->has('search') && !empty($request->search)) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('sku', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }

                // Apply category filter
                if ($request->has('category_id') && !empty($request->category_id)) {
                    $query->where('category_id', $request->category_id);
                }

                // Apply pagination for orders page
                $perPage = $request->get('per_page', 10);
                $products = $query->orderBy('name')->paginate($perPage);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'data' => $products->items(),
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total(),
                    ]
                ]);
            }
        }

        // Regular view response
        $products = Product::with(['category'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $categories = Category::active()->get();
        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:100|unique:products',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'allow_installments' => 'boolean',
            'max_installments' => 'required_if:allow_installments,true|integer|min:2|max:60',
            'installment_interest_rate' => 'required_if:allow_installments,true|numeric|min:0|max:100',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        
        // Generate SKU if not provided
        if (empty($data['sku'] ?? null)) {
            $data['sku'] = 'PRD-' . strtoupper(Str::random(8));
        }

        // Handle multiple image uploads
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
            $data['images'] = $images;
        }

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully!',
            'data' => $product->load('category'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product, Request $request)
    {
        $product->load(['category', 'orderItems.order']);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $product,
            ]);
        }

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'allow_installments' => 'boolean',
            'max_installments' => 'required_if:allow_installments,true|integer|min:2|max:60',
            'installment_interest_rate' => 'required_if:allow_installments,true|numeric|min:0|max:100',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            // Delete old images
            if ($product->images) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
            
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
            $data['images'] = $images;
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully!',
            'data' => $product->load('category'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            // Check if product has orders
            if ($product->orderItems()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product with existing orders.',
                ], 400);
            }

            // Delete images if exist
            if ($product->images) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update product stock
     */
    public function updateStock(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stock_quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $product->update(['stock_quantity' => $request->stock_quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully!',
            'data' => $product,
        ]);
    }

    /**
     * Get products for dropdown/select
     */
    public function getForSelect(Request $request): JsonResponse
    {
        $products = Product::active()
            ->inStock()
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock_quantity', 'allow_installments']);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}
