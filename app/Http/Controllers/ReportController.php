<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

/**
 * Report Controller
 *
 * Handles essential reporting for the e-commerce system.
 * Provides key reports: Sales, Inventory, Orders, and Customers with PDF export.
 */
class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display reports index page
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Sales Report - Summary of sales with date range
     */
    public function sales(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        if ($request->expectsJson()) {
            return $this->getSalesData($dateFrom, $dateTo);
        }

        return view('reports.sales', compact('dateFrom', 'dateTo'));
    }

    /**
     * Inventory Report - Current stock levels
     */
    public function inventory(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->getInventoryData($request);
        }

        return view('reports.inventory');
    }

    /**
     * Orders Report - Order details and status
     */
    public function orders(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        if ($request->expectsJson()) {
            return $this->getOrdersData($dateFrom, $dateTo, $request);
        }

        return view('reports.orders', compact('dateFrom', 'dateTo'));
    }

    /**
     * Customers Report - Customer list and order history
     */
    public function customers(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        if ($request->expectsJson()) {
            return $this->getCustomersData($dateFrom, $dateTo);
        }

        return view('reports.customers', compact('dateFrom', 'dateTo'));
    }

    /**
     * Product Performance Report - Best selling products and profit analysis
     */
    public function productPerformance(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        if ($request->expectsJson()) {
            return $this->getProductPerformanceData($dateFrom, $dateTo, $request);
        }

        return view('reports.product-performance', compact('dateFrom', 'dateTo'));
    }

    /**
     * Category Performance Report - Category sales analysis
     */
    public function categoryPerformance(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        if ($request->expectsJson()) {
            return $this->getCategoryPerformanceData($dateFrom, $dateTo);
        }

        return view('reports.category-performance', compact('dateFrom', 'dateTo'));
    }

    /**
     * Payment Methods Report - Payment method analysis
     */
    public function paymentMethods(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        if ($request->expectsJson()) {
            return $this->getPaymentMethodsData($dateFrom, $dateTo);
        }

        return view('reports.payment-methods', compact('dateFrom', 'dateTo'));
    }

    /**
     * Installment Report - Installment payment tracking
     */
    public function installments(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->getInstallmentsData($request);
        }

        return view('reports.installments');
    }

    /**
     * Low Stock Alert Report - Products needing restock
     */
    public function lowStock(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->getLowStockData($request);
        }

        return view('reports.low-stock');
    }

    /**
     * Revenue Trends Report - Daily/weekly/monthly revenue analysis
     */
    public function revenueTrends(Request $request)
    {
        $period = $request->period ?? 'daily';
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        if ($request->expectsJson()) {
            return $this->getRevenueTrendsData($dateFrom, $dateTo, $period);
        }

        return view('reports.revenue-trends', compact('dateFrom', 'dateTo', 'period'));
    }

    /**
     * Export report as PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $reportType = $request->type;
            $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

            switch ($reportType) {
                case 'sales':
                    return $this->exportSalesPdf($dateFrom, $dateTo);
                case 'inventory':
                    return $this->exportInventoryPdf($request);
                case 'orders':
                    return $this->exportOrdersPdf($dateFrom, $dateTo, $request);
                case 'customers':
                    return $this->exportCustomersPdf($dateFrom, $dateTo);
                case 'product-performance':
                    return $this->exportProductPerformancePdf($dateFrom, $dateTo, $request);
                case 'category-performance':
                    return $this->exportCategoryPerformancePdf($dateFrom, $dateTo);
                case 'payment-methods':
                    return $this->exportPaymentMethodsPdf($dateFrom, $dateTo);
                case 'installments':
                    return $this->exportInstallmentsPdf($request);
                case 'low-stock':
                    return $this->exportLowStockPdf($request);
                case 'revenue-trends':
                    return $this->exportRevenueTrendsPdf($dateFrom, $dateTo, $request->period ?? 'daily');
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid report type'], 400);
            }
        } catch (\Exception $e) {
            Log::error('PDF Export error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Export failed'], 500);
        }
    }

    /**
     * Get sales data
     */
    private function getSalesData($dateFrom, $dateTo): JsonResponse
    {
        $sales = Order::with(['user', 'orderItems.product'])
            ->whereIn('status', ['processing', 'shipped', 'delivered'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_orders' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'total_items' => $sales->sum(function ($order) {
                return $order->orderItems->sum('quantity');
            }),
            'average_order_value' => $sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $sales,
                'summary' => $summary,
                'period' => ['from' => $dateFrom, 'to' => $dateTo],
            ],
        ]);
    }

    /**
     * Get inventory data
     */
    private function getInventoryData(Request $request): JsonResponse
    {
        $products = Product::with('category')
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($request->stock_status, function ($query, $status) {
                if ($status === 'low') {
                    return $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
                } elseif ($status === 'out') {
                    return $query->where('stock_quantity', 0);
                } elseif ($status === 'good') {
                    return $query->whereColumn('stock_quantity', '>', 'min_stock_level');
                }
                return $query;
            })
            ->orderBy('name')
            ->get();

        $summary = [
            'total_products' => $products->count(),
            'total_value' => $products->sum(function ($product) {
                return $product->stock_quantity * $product->cost_price;
            }),
            'low_stock' => $products->where('stock_quantity', '<=', DB::raw('min_stock_level'))->count(),
            'out_of_stock' => $products->where('stock_quantity', 0)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * Get orders data
     */
    private function getOrdersData($dateFrom, $dateTo, Request $request): JsonResponse
    {
        $orders = Order::with(['user', 'orderItems.product'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->payment_type, function ($query, $paymentType) {
                return $query->where('payment_type', $paymentType);
            })
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'processing_orders' => $orders->where('status', 'processing')->count(),
            'shipped_orders' => $orders->where('status', 'shipped')->count(),
            'delivered_orders' => $orders->where('status', 'delivered')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders,
                'summary' => $summary,
                'period' => ['from' => $dateFrom, 'to' => $dateTo],
            ],
        ]);
    }

    /**
     * Get customers data
     */
    private function getCustomersData($dateFrom, $dateTo): JsonResponse
    {
        $customers = User::where('user_type', 'customer')
            ->with(['orders' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereDate('created_at', '>=', $dateFrom)
                      ->whereDate('created_at', '<=', $dateTo);
            }])
            ->withCount(['orders' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereDate('created_at', '>=', $dateFrom)
                      ->whereDate('created_at', '<=', $dateTo);
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'total_orders' => $customer->orders_count,
                    'total_spent' => $customer->orders->sum('total_amount'),
                    'last_order_date' => $customer->orders->max('created_at'),
                ];
            });

        $summary = [
            'total_customers' => $customers->count(),
            'active_customers' => $customers->where('total_orders', '>', 0)->count(),
            'total_revenue' => $customers->sum('total_spent'),
            'average_order_value' => $customers->where('total_orders', '>', 0)->avg('total_spent'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'customers' => $customers,
                'summary' => $summary,
                'period' => ['from' => $dateFrom, 'to' => $dateTo],
            ],
        ]);
    }

    /**
     * Get product performance data
     */
    private function getProductPerformanceData($dateFrom, $dateTo, Request $request): JsonResponse
    {
        // Get current tenant connection
        $tenantConnection = config('multitenancy.tenant_database_connection_name') ?? 'tenant';

        $products = DB::connection($tenantConnection)
            ->table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('orders.status', ['processing', 'shipped', 'delivered'])
            ->whereDate('orders.created_at', '>=', $dateFrom)
            ->whereDate('orders.created_at', '<=', $dateTo)
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('products.category_id', $categoryId);
            })
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.cost_price',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.price', 'products.cost_price', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->map(function ($product) {
                $totalCost = $product->total_sold * $product->cost_price;
                $profit = $product->total_revenue - $totalCost;
                $profitMargin = $product->total_revenue > 0 ? ($profit / $product->total_revenue) * 100 : 0;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category_name,
                    'price' => $product->price,
                    'cost_price' => $product->cost_price,
                    'total_sold' => $product->total_sold,
                    'total_revenue' => $product->total_revenue,
                    'order_count' => $product->order_count,
                    'total_cost' => $totalCost,
                    'profit' => $profit,
                    'profit_margin' => $profitMargin,
                ];
            });

        $summary = [
            'total_products' => $products->count(),
            'total_revenue' => $products->sum('total_revenue'),
            'total_profit' => $products->sum('profit'),
            'average_profit_margin' => $products->avg('profit_margin'),
            'best_seller' => $products->first(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'summary' => $summary,
                'period' => ['from' => $dateFrom, 'to' => $dateTo],
            ],
        ]);
    }

    /**
     * Get category performance data
     */
    private function getCategoryPerformanceData($dateFrom, $dateTo): JsonResponse
    {
        // Get current tenant connection
        $tenantConnection = config('multitenancy.tenant_database_connection_name') ?? 'tenant';

        $categories = DB::connection($tenantConnection)
            ->table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['processing', 'shipped', 'delivered'])
            ->whereDate('orders.created_at', '>=', $dateFrom)
            ->whereDate('orders.created_at', '<=', $dateTo)
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT products.id) as product_count'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        $summary = [
            'total_categories' => $categories->count(),
            'total_revenue' => $categories->sum('total_revenue'),
            'total_sold' => $categories->sum('total_sold'),
            'best_category' => $categories->first(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'summary' => $summary,
                'period' => ['from' => $dateFrom, 'to' => $dateTo],
            ],
        ]);
    }

    /**
     * Get payment methods data
     */
    private function getPaymentMethodsData($dateFrom, $dateTo): JsonResponse
    {
        // Get current tenant connection
        $tenantConnection = config('multitenancy.tenant_database_connection_name') ?? 'tenant';

        $paymentMethods = DB::connection($tenantConnection)
            ->table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('payments.status', 'completed')
            ->whereDate('payments.processed_at', '>=', $dateFrom)
            ->whereDate('payments.processed_at', '<=', $dateTo)
            ->select(
                'payments.payment_method',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(payments.amount) as total_amount'),
                DB::raw('AVG(payments.amount) as average_amount'),
                DB::raw('COUNT(DISTINCT payments.order_id) as order_count')
            )
            ->groupBy('payments.payment_method')
            ->orderBy('total_amount', 'desc')
            ->get();

        $summary = [
            'total_methods' => $paymentMethods->count(),
            'total_transactions' => $paymentMethods->sum('transaction_count'),
            'total_amount' => $paymentMethods->sum('total_amount'),
            'most_used' => $paymentMethods->first(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'payment_methods' => $paymentMethods,
                'summary' => $summary,
                'period' => ['from' => $dateFrom, 'to' => $dateTo],
            ],
        ]);
    }

    /**
     * Get installments data
     */
    private function getInstallmentsData(Request $request): JsonResponse
    {
        // Get current tenant connection
        $tenantConnection = config('multitenancy.tenant_database_connection_name') ?? 'tenant';

        $installments = DB::connection($tenantConnection)
            ->table('installments')
            ->join('orders', 'installments.order_id', '=', 'orders.id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select(
                'installments.*',
                'orders.order_number',
                'users.name as customer_name',
                'users.email as customer_email'
            )
            ->when($request->status, function ($query, $status) {
                return $query->where('installments.status', $status);
            })
            ->when($request->overdue_only, function ($query) {
                return $query->where('installments.status', 'pending')
                            ->where('installments.due_date', '<', now());
            })
            ->orderBy('installments.due_date', 'asc')
            ->get();

        $summary = [
            'total_installments' => $installments->count(),
            'pending_amount' => $installments->where('status', 'pending')->sum('total_amount'),
            'paid_amount' => $installments->where('status', 'paid')->sum('total_amount'),
            'overdue_count' => $installments->where('status', 'pending')
                                          ->where('due_date', '<', now())->count(),
            'overdue_amount' => $installments->where('status', 'pending')
                                           ->where('due_date', '<', now())->sum('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'installments' => $installments,
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * Get low stock data
     */
    private function getLowStockData(Request $request): JsonResponse
    {
        $products = Product::with('category')
            ->where(function ($query) {
                $query->whereColumn('stock_quantity', '<=', 'min_stock_level')
                      ->orWhere('stock_quantity', 0);
            })
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->orderBy('stock_quantity', 'asc')
            ->get();

        $summary = [
            'total_low_stock' => $products->where('stock_quantity', '>', 0)->count(),
            'total_out_of_stock' => $products->where('stock_quantity', 0)->count(),
            'total_value_at_risk' => $products->sum(function ($product) {
                return $product->stock_quantity * $product->cost_price;
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * Get revenue trends data
     */
    private function getRevenueTrendsData($dateFrom, $dateTo, $period): JsonResponse
    {
        // Get current tenant connection
        $tenantConnection = config('multitenancy.tenant_database_connection_name') ?? 'tenant';

        $dateFormat = match($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $trends = DB::connection($tenantConnection)
            ->table('orders')
            ->whereIn('status', ['processing', 'shipped', 'delivered'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->select(
                DB::raw("strftime('$dateFormat', created_at) as period"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $summary = [
            'total_periods' => $trends->count(),
            'total_revenue' => $trends->sum('revenue'),
            'total_orders' => $trends->sum('order_count'),
            'average_revenue_per_period' => $trends->avg('revenue'),
            'best_period' => $trends->sortByDesc('revenue')->first(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'trends' => $trends,
                'summary' => $summary,
                'period' => ['from' => $dateFrom, 'to' => $dateTo, 'type' => $period],
            ],
        ]);
    }

    /**
     * Export sales report as PDF
     */
    private function exportSalesPdf($dateFrom, $dateTo)
    {
        $data = $this->getSalesData($dateFrom, $dateTo)->getData(true)['data'];

        $pdf = Pdf::loadView('reports.pdf.sales', [
            'orders' => $data['orders'],
            'summary' => $data['summary'],
            'period' => $data['period'],
        ]);

        $filename = "sales_report_{$dateFrom}_to_{$dateTo}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export inventory report as PDF
     */
    private function exportInventoryPdf(Request $request)
    {
        $data = $this->getInventoryData($request)->getData(true)['data'];

        $pdf = Pdf::loadView('reports.pdf.inventory', [
            'products' => $data['products'],
            'summary' => $data['summary'],
        ]);

        $filename = "inventory_report_" . date('Y-m-d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Export orders report as PDF
     */
    private function exportOrdersPdf($dateFrom, $dateTo, Request $request)
    {
        $data = $this->getOrdersData($dateFrom, $dateTo, $request)->getData(true)['data'];

        $pdf = Pdf::loadView('reports.pdf.orders', [
            'orders' => $data['orders'],
            'summary' => $data['summary'],
            'period' => $data['period'],
        ]);

        $filename = "orders_report_{$dateFrom}_to_{$dateTo}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export customers report as PDF
     */
    private function exportCustomersPdf($dateFrom, $dateTo)
    {
        $data = $this->getCustomersData($dateFrom, $dateTo)->getData(true)['data'];

        $pdf = Pdf::loadView('reports.pdf.customers', [
            'customers' => $data['customers'],
            'summary' => $data['summary'],
            'period' => $data['period'],
        ]);

        $filename = "customers_report_{$dateFrom}_to_{$dateTo}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export product performance report as PDF
     */
    private function exportProductPerformancePdf($dateFrom, $dateTo, Request $request)
    {
        $data = $this->getProductPerformanceData($dateFrom, $dateTo, $request)->getData(true)['data'];

        $pdf = Pdf::loadView('reports.pdf.product-performance', [
            'products' => $data['products'],
            'summary' => $data['summary'],
            'period' => $data['period'],
        ]);

        $filename = "product_performance_{$dateFrom}_to_{$dateTo}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export category performance report as PDF
     */
    private function exportCategoryPerformancePdf($dateFrom, $dateTo)
    {
        $data = $this->getCategoryPerformanceData($dateFrom, $dateTo)->getData(true)['data'];

        $pdf = Pdf::loadView('reports.pdf.category-performance', [
            'categories' => $data['categories'],
            'summary' => $data['summary'],
            'period' => $data['period'],
        ]);

        $filename = "category_performance_{$dateFrom}_to_{$dateTo}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export payment methods report as PDF
     */
    private function exportPaymentMethodsPdf($dateFrom, $dateTo)
    {
        $data = $this->getPaymentMethodsData($dateFrom, $dateTo)->getData(true)['data'];

        $pdf = Pdf::loadView('reports.pdf.payment-methods', [
            'payment_methods' => $data['payment_methods'],
            'summary' => $data['summary'],
            'period' => $data['period'],
        ]);

        $filename = "payment_methods_{$dateFrom}_to_{$dateTo}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export installments report as PDF
     */
    private function exportInstallmentsPdf(Request $request)
    {
        $data = $this->getInstallmentsData($request)->getData(true)['data'];

        $pdf = Pdf::loadView('reports.pdf.installments', [
            'installments' => $data['installments'],
            'summary' => $data['summary'],
        ]);

        $filename = "installments_report_" . date('Y-m-d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Export low stock report as PDF
     */
    private function exportLowStockPdf(Request $request)
    {
        $data = $this->getLowStockData($request)->getData(true)['data'];

        $pdf = Pdf::loadView('reports.pdf.low-stock', [
            'products' => $data['products'],
            'summary' => $data['summary'],
        ]);

        $filename = "low_stock_report_" . date('Y-m-d') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Export revenue trends report as PDF
     */
    private function exportRevenueTrendsPdf($dateFrom, $dateTo, $period)
    {
        $data = $this->getRevenueTrendsData($dateFrom, $dateTo, $period)->getData(true)['data'];

        $pdf = Pdf::loadView('reports.pdf.revenue-trends', [
            'trends' => $data['trends'],
            'summary' => $data['summary'],
            'period' => $data['period'],
        ]);

        $filename = "revenue_trends_{$dateFrom}_to_{$dateTo}.pdf";

        return $pdf->download($filename);
    }
}
