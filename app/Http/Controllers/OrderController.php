<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Installment;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product', 'payments'])
            ->when($request->search, function ($q, $search) {
                return $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                     ->orWhere('email', 'like', "%{$search}%");
                        });
            })
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->payment_type, function ($q, $paymentType) {
                return $q->where('payment_type', $paymentType);
            })
            ->when($request->date_from, function ($q, $dateFrom) {
                return $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($q, $dateTo) {
                return $q->whereDate('created_at', '<=', $dateTo);
            });

        // For customers, only show their own orders
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        }

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::active()->inStock()->with('category')->get();
        $categories = Category::active()->get();
        return view('orders.create', compact('products', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_type' => 'required|in:instant,installment',
            'installments' => 'required_if:payment_type,installment|integer|min:2|max:60',
            'shipping_address' => 'required|array',
            'shipping_address.address' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.zip_code' => 'required|string',
            'billing_address' => 'required|array',
            'payment_method' => 'required|in:credit_card,debit_card,bank_transfer,cash,digital_wallet',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate order totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Check stock availability
                if (!$product->isInStock($item['quantity'])) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $lineTotal = $product->price * $item['quantity'];
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $lineTotal,
                ];
            }

            // Calculate taxes and total
            $taxRate = 0.10; // 10% tax rate
            $taxAmount = $subtotal * $taxRate;
            $shippingCost = $subtotal > 100 ? 0 : 10; // Free shipping over $100
            $totalAmount = $subtotal + $taxAmount + $shippingCost;

            // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'payment_type' => $request->payment_type,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'notes' => $request->notes,
            ]);

            // Create order items and decrease stock
            foreach ($orderItems as $itemData) {
                $order->orderItems()->create($itemData);
                
                // Decrease product stock
                $product = Product::find($itemData['product_id']);
                $product->decreaseStock($itemData['quantity']);
            }

            // Handle installment setup if payment type is installment
            if ($request->payment_type === 'installment') {
                $this->createInstallments($order, $request->installments);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully!',
                'data' => $order->load(['orderItems.product', 'user']),
                'order_id' => $order->id,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order, Request $request)
    {
        // Check if user can view this order
        if (!Auth::user()->isAdmin() && $order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order.');
        }

        $order->load([
            'user',
            'orderItems.product.category',
            'payments',
            'installments' => function ($query) {
                $query->orderBy('installment_number');
            }
        ]);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        }

        return view('orders.show', compact('order'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Handle status-specific logic
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'shipped' && $oldStatus !== 'shipped') {
            $updateData['shipped_at'] = now();
        }

        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
            $updateData['delivered_at'] = now();
        }

        // If cancelling order, restore stock
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            foreach ($order->orderItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increaseStock($item->quantity);
                }
            }
        }

        $order->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully!',
            'data' => $order->load(['orderItems.product', 'user']),
        ]);
    }

    /**
     * Create installments for an order
     */
    private function createInstallments(Order $order, int $installmentCount)
    {
        $installmentAmount = $order->total_amount / $installmentCount;
        
        for ($i = 1; $i <= $installmentCount; $i++) {
            $dueDate = Carbon::now()->addMonths($i);
            
            Installment::create([
                'order_id' => $order->id,
                'installment_number' => $i,
                'amount' => $installmentAmount,
                'interest_amount' => 0, // Calculate if needed
                'total_amount' => $installmentAmount,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Get order statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $query = Order::query();
        
        // Filter by date range if provided
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // For customers, only their orders
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        $stats = [
            'total_orders' => $query->count(),
            'pending_orders' => (clone $query)->where('status', 'pending')->count(),
            'processing_orders' => (clone $query)->where('status', 'processing')->count(),
            'shipped_orders' => (clone $query)->where('status', 'shipped')->count(),
            'delivered_orders' => (clone $query)->where('status', 'delivered')->count(),
            'cancelled_orders' => (clone $query)->where('status', 'cancelled')->count(),
            'total_revenue' => (clone $query)->whereIn('status', ['delivered', 'shipped'])->sum('total_amount'),
            'instant_payments' => (clone $query)->where('payment_type', 'instant')->count(),
            'installment_payments' => (clone $query)->where('payment_type', 'installment')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Add items to cart (for AJAX shopping cart)
     */
    public function addToCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);
        
        if (!$product->isInStock($request->quantity)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available.',
            ], 400);
        }

        // Store cart in session
        $cart = session('cart', []);
        $productId = $request->product_id;
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $request->quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $request->quantity,
                'image' => $product->main_image,
            ];
        }

        session(['cart' => $cart]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart!',
            'cart_count' => array_sum(array_column($cart, 'quantity')),
            'cart' => $cart,
        ]);
    }

    /**
     * Get cart contents
     */
    public function getCart(): JsonResponse
    {
        $cart = session('cart', []);
        $cartTotal = 0;
        
        foreach ($cart as &$item) {
            $item['total'] = $item['price'] * $item['quantity'];
            $cartTotal += $item['total'];
        }

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'cart_total' => $cartTotal,
            'cart_count' => array_sum(array_column($cart, 'quantity')),
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $cart = session('cart', []);
        $productId = $request->product_id;

        if ($request->quantity == 0) {
            unset($cart[$productId]);
        } else {
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $request->quantity;
            }
        }

        session(['cart' => $cart]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully!',
            'cart_count' => array_sum(array_column($cart, 'quantity')),
        ]);
    }

    /**
     * Clear cart
     */
    public function clearCart(): JsonResponse
    {
        session()->forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully!',
        ]);
    }

    /**
     * Checkout process
     */
    public function checkout(Request $request): JsonResponse
    {
        $cart = session('cart', []);
        
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'payment_type' => 'required|in:instant,installment',
            'installments' => 'required_if:payment_type,installment|integer|min:2|max:60',
            'shipping_address' => 'required|array',
            'billing_address' => 'required|array',
            'payment_method' => 'required|in:credit_card,debit_card,bank_transfer,cash,digital_wallet',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create order from cart
            $items = [];
            foreach ($cart as $item) {
                $items[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ];
            }

            $orderData = array_merge($request->all(), ['items' => $items]);
            $orderRequest = new Request($orderData);
            
            // Use store method to create order
            $response = $this->store($orderRequest);
            $responseData = $response->getData(true);
            
            if (!$responseData['success']) {
                throw new \Exception($responseData['message']);
            }

            // Clear cart after successful order
            session()->forget('cart');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $responseData['order_id'],
                'data' => $responseData['data'],
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error during checkout: ' . $e->getMessage(),
            ], 500);
        }
    }
}
