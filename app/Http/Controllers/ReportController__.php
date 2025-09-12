<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Installment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Report Controller
 *
 * Handles financial and analytical reporting for the e-commerce system.
 * Provides comprehensive reports including ledger, balance sheet, profit & loss,
 * sales analytics, inventory, and customer analytics.
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
     * Display the ledger report.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function ledger(Request $request)
    {
        try {
            $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

            // Validate date range
            if (Carbon::parse($dateFrom)->gt(Carbon::parse($dateTo))) {
                return back()->with('error', 'Start date cannot be after end date.');
            }

            if ($request->expectsJson()) {
                return $this->getLedgerData($dateFrom, $dateTo);
            }

            return view('reports.ledger', compact('dateFrom', 'dateTo'));
        } catch (\Exception $e) {
            Log::error('Error loading ledger report: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load ledger report.',
                ], 500);
            }

            return back()->with('error', 'Failed to load ledger report.');
        }
    }

    /**
     * Show balance sheet
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->as_of_date ?? Carbon::now()->format('Y-m-d');

        if ($request->expectsJson()) {
            return $this->getBalanceSheetData($asOfDate);
        }

        return view('reports.balance-sheet', compact('asOfDate'));
    }

    /**
     * Show profit & loss statement
     */
    public function profitLoss(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        if ($request->expectsJson()) {
            return $this->getProfitLossData($dateFrom, $dateTo);
        }

        return view('reports.profit-loss', compact('dateFrom', 'dateTo'));
    }

    /**
     * Get ledger data
     */
    private function getLedgerData($dateFrom, $dateTo): JsonResponse
    {
        // Get all financial transactions
        $transactions = collect();

        // Sales transactions (Credit)
        $sales = Payment::with(['order', 'user'])
            ->where('status', 'completed')
            ->whereDate('processed_at', '>=', $dateFrom)
            ->whereDate('processed_at', '<=', $dateTo)
            ->where('amount', '>', 0)
            ->get()
            ->map(function ($payment) {
                return [
                    'date' => $payment->processed_at->format('Y-m-d'),
                    'description' => "Payment received for Order #{$payment->order->order_number}",
                    'reference' => $payment->payment_number,
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'balance' => 0, // Will be calculated
                    'type' => 'payment',
                    'customer' => $payment->user->name ?? 'Unknown',
                ];
            });

        // Refund transactions (Debit)
        $refunds = Payment::with(['order', 'user'])
            ->where('status', 'completed')
            ->where('payment_type', 'refund')
            ->whereDate('processed_at', '>=', $dateFrom)
            ->whereDate('processed_at', '<=', $dateTo)
            ->get()
            ->map(function ($payment) {
                return [
                    'date' => $payment->processed_at->format('Y-m-d'),
                    'description' => "Refund for Order #{$payment->order->order_number}",
                    'reference' => $payment->payment_number,
                    'debit' => abs($payment->amount),
                    'credit' => 0,
                    'balance' => 0,
                    'type' => 'refund',
                    'customer' => $payment->user->name ?? 'Unknown',
                ];
            });

        // Combine and sort transactions
        $transactions = $sales->concat($refunds)->sortBy('date')->values();

        // Calculate running balance
        $runningBalance = 0;
        $transactions = $transactions->map(function ($transaction) use (&$runningBalance) {
            $runningBalance += ($transaction['credit'] - $transaction['debit']);
            $transaction['balance'] = $runningBalance;
            return $transaction;
        });

        $summary = [
            'total_debits' => $transactions->sum('debit'),
            'total_credits' => $transactions->sum('credit'),
            'net_change' => $transactions->sum('credit') - $transactions->sum('debit'),
            'closing_balance' => $runningBalance,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $transactions,
                'summary' => $summary,
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
            ],
        ]);
    }

    /**
     * Get balance sheet data
     */
    private function getBalanceSheetData($asOfDate): JsonResponse
    {
        // Assets
        $assets = [
            'current_assets' => [
                'cash' => Payment::where('status', 'completed')
                    ->where('amount', '>', 0)
                    ->whereDate('processed_at', '<=', $asOfDate)
                    ->sum('amount') -
                    Payment::where('status', 'completed')
                    ->where('payment_type', 'refund')
                    ->whereDate('processed_at', '<=', $asOfDate)
                    ->sum(DB::raw('ABS(amount)')),
                'inventory' => Product::whereDate('created_at', '<=', $asOfDate)
                    ->sum(DB::raw('stock_quantity * cost_price')),
                'accounts_receivable' => Installment::where('status', 'pending')
                    ->whereHas('order', function ($query) use ($asOfDate) {
                        $query->whereDate('created_at', '<=', $asOfDate);
                    })
                    ->sum('total_amount'),
            ],
        ];

        $assets['current_assets']['total'] = array_sum($assets['current_assets']);
        $assets['total_assets'] = $assets['current_assets']['total'];

        // Liabilities
        $liabilities = [
            'current_liabilities' => [
                'accounts_payable' => 0, // Could be supplier invoices
                'accrued_expenses' => 0,
            ],
        ];

        $liabilities['current_liabilities']['total'] = array_sum($liabilities['current_liabilities']);
        $liabilities['total_liabilities'] = $liabilities['current_liabilities']['total'];

        // Equity
        $equity = [
            'retained_earnings' => $assets['total_assets'] - $liabilities['total_liabilities'],
        ];
        $equity['total_equity'] = $equity['retained_earnings'];

        return response()->json([
            'success' => true,
            'data' => [
                'assets' => $assets,
                'liabilities' => $liabilities,
                'equity' => $equity,
                'as_of_date' => $asOfDate,
            ],
        ]);
    }

    /**
     * Get profit & loss data
     */
    private function getProfitLossData($dateFrom, $dateTo): JsonResponse
    {
        // Revenue
        $revenue = [
            'sales_revenue' => Payment::where('status', 'completed')
                ->where('amount', '>', 0)
                ->whereDate('processed_at', '>=', $dateFrom)
                ->whereDate('processed_at', '<=', $dateTo)
                ->sum('amount'),
            'refunds' => Payment::where('status', 'completed')
                ->where('payment_type', 'refund')
                ->whereDate('processed_at', '>=', $dateFrom)
                ->whereDate('processed_at', '<=', $dateTo)
                ->sum(DB::raw('ABS(amount)')),
        ];

        $revenue['net_revenue'] = $revenue['sales_revenue'] - $revenue['refunds'];

        // Cost of Goods Sold
        $cogs = OrderItem::whereHas('order', function ($query) use ($dateFrom, $dateTo) {
            $query->whereIn('status', ['processing', 'shipped', 'delivered'])
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo);
        })
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->sum(DB::raw('order_items.quantity * products.cost_price'));

        // Gross Profit
        $grossProfit = $revenue['net_revenue'] - $cogs;

        // Operating Expenses (simplified)
        $expenses = [
            'shipping_costs' => Order::whereIn('status', ['processing', 'shipped', 'delivered'])
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->sum('shipping_cost'),
            'processing_fees' => $revenue['sales_revenue'] * 0.025, // 2.5% processing fee
            'other_expenses' => 0,
        ];

        $expenses['total_expenses'] = array_sum($expenses);

        // Net Profit
        $netProfit = $grossProfit - $expenses['total_expenses'];

        // Profit margins
        $profitMargins = [
            'gross_margin' => $revenue['net_revenue'] > 0 ? ($grossProfit / $revenue['net_revenue']) * 100 : 0,
            'net_margin' => $revenue['net_revenue'] > 0 ? ($netProfit / $revenue['net_revenue']) * 100 : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'revenue' => $revenue,
                'cogs' => $cogs,
                'gross_profit' => $grossProfit,
                'expenses' => $expenses,
                'net_profit' => $netProfit,
                'profit_margins' => $profitMargins,
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
            ],
        ]);
    }

    /**
     * Get sales analytics
     */
    public function salesAnalytics(Request $request)
    {
        try {
            $period = $request->period ?? 'month'; // day, week, month, year
            $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

            if ($request->expectsJson()) {
                // Sales by period
                $salesByPeriod = Order::selectRaw(
                    "strftime('%Y-%m-%d', created_at) as period,
                        COUNT(*) as orders_count,
                        SUM(total_amount) as total_sales"
                )
                    ->whereIn('status', ['processing', 'shipped', 'delivered'])
                    ->whereDate('created_at', '>=', $dateFrom)
                    ->whereDate('created_at', '<=', $dateTo)
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();

                // Top products
                $topProducts = OrderItem::select(
                    'products.name',
                    DB::raw('SUM(order_items.quantity) as total_sold'),
                    DB::raw('SUM(order_items.total_price) as total_revenue')
                )
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereIn('orders.status', ['processing', 'shipped', 'delivered'])
                    ->whereDate('orders.created_at', '>=', $dateFrom)
                    ->whereDate('orders.created_at', '<=', $dateTo)
                    ->groupBy('products.id', 'products.name')
                    ->orderBy('total_revenue', 'desc')
                    ->limit(10)
                    ->get();

                // Sales by category
                $salesByCategory = OrderItem::select(
                    'categories.name',
                    DB::raw('SUM(order_items.quantity) as total_sold'),
                    DB::raw('SUM(order_items.total_price) as total_revenue')
                )
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->join('categories', 'products.category_id', '=', 'categories.id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereIn('orders.status', ['processing', 'shipped', 'delivered'])
                    ->whereDate('orders.created_at', '>=', $dateFrom)
                    ->whereDate('orders.created_at', '<=', $dateTo)
                    ->groupBy('categories.id', 'categories.name')
                    ->orderBy('total_revenue', 'desc')
                    ->get();

                // Payment method breakdown
                $paymentMethods = Payment::select(
                    'payment_method',
                    DB::raw('COUNT(*) as transaction_count'),
                    DB::raw('SUM(amount) as total_amount')
                )
                    ->where('status', 'completed')
                    ->where('amount', '>', 0)
                    ->whereDate('processed_at', '>=', $dateFrom)
                    ->whereDate('processed_at', '<=', $dateTo)
                    ->groupBy('payment_method')
                    ->orderBy('total_amount', 'desc')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'sales_by_period' => $salesByPeriod,
                        'top_products' => $topProducts,
                        'sales_by_category' => $salesByCategory,
                        'payment_methods' => $paymentMethods,
                        'period' => [
                            'from' => $dateFrom,
                            'to' => $dateTo,
                        ],
                    ],
                ]);
            }

            return view('reports.sales-analytics', compact('dateFrom', 'dateTo'));
        } catch (\Exception $e) {
            Log::error('Sales analytics error: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load sales analytics.',
                ], 500);
            }

            return back()->with('error', 'Failed to load sales analytics.');
        }
    }

    /**
     * Get inventory report
     */
    public function inventoryReport(Request $request)
    {
        try {
            if ($request->expectsJson()) {
                $products = Product::with('category')
                    ->select([
                        'id',
                        'name',
                        'sku',
                        'stock_quantity',
                        'min_stock_level',
                        'cost_price',
                        'price',
                        'category_id',
                        DB::raw('stock_quantity * cost_price as inventory_value'),
                        DB::raw('CASE WHEN stock_quantity <= min_stock_level THEN "low" WHEN stock_quantity = 0 THEN "out" ELSE "good" END as stock_status')
                    ])
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
                    'total_inventory_value' => $products->sum('inventory_value'),
                    'low_stock_items' => $products->where('stock_status', 'low')->count(),
                    'out_of_stock_items' => $products->where('stock_status', 'out')->count(),
                ];

                return response()->json([
                    'success' => true,
                    'data' => [
                        'products' => $products,
                        'summary' => $summary,
                    ],
                ]);
            }

            return view('reports.inventory');
        } catch (\Exception $e) {
            Log::error('Inventory report error: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load inventory report.',
                ], 500);
            }

            return back()->with('error', 'Failed to load inventory report.');
        }
    }

    /**
     * Get customer analytics
     */
    public function customerAnalytics(Request $request)
    {
        try {
            $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

            if ($request->expectsJson()) {
                // Top customers by revenue
                $topCustomers = Order::select(
                    'users.name',
                    'users.email',
                    DB::raw('COUNT(orders.id) as total_orders'),
                    DB::raw('SUM(orders.total_amount) as total_spent')
                )
                    ->join('users', 'orders.user_id', '=', 'users.id')
                    ->whereIn('orders.status', ['processing', 'shipped', 'delivered'])
                    ->whereDate('orders.created_at', '>=', $dateFrom)
                    ->whereDate('orders.created_at', '<=', $dateTo)
                    ->groupBy('users.id', 'users.name', 'users.email')
                    ->orderBy('total_spent', 'desc')
                    ->limit(10)
                    ->get();

                // Customer acquisition
                $newCustomers = DB::table('users')
                    ->selectRaw("strftime('%Y-%m-%d', created_at) as period, COUNT(*) as new_customers")
                    ->where('user_type', 'customer')
                    ->whereDate('created_at', '>=', $dateFrom)
                    ->whereDate('created_at', '<=', $dateTo)
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();

                // Payment type preferences
                $paymentTypeStats = Order::select(
                    'payment_type',
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as total_amount')
                )
                    ->whereIn('status', ['processing', 'shipped', 'delivered'])
                    ->whereDate('created_at', '>=', $dateFrom)
                    ->whereDate('created_at', '<=', $dateTo)
                    ->groupBy('payment_type')
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'top_customers' => $topCustomers,
                        'new_customers' => $newCustomers,
                        'payment_type_stats' => $paymentTypeStats,
                        'period' => [
                            'from' => $dateFrom,
                            'to' => $dateTo,
                        ],
                    ],
                ]);
            }

            return view('reports.customers', compact('dateFrom', 'dateTo'));
        } catch (\Exception $e) {
            Log::error('Customer analytics error: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load customer analytics.',
                ], 500);
            }

            return back()->with('error', 'Failed to load customer analytics.');
        }
    }

    /**
     * Export report to CSV
     */
    public function exportReport(Request $request)
    {
        try {
            $reportType = $request->type; // ledger, balance-sheet, profit-loss, sales-analytics, inventory, customers
            $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $dateTo = $request->date_to ?? Carbon::now()->endOfMonth()->format('Y-m-d');

            switch ($reportType) {
                case 'ledger':
                    return $this->exportLedger($dateFrom, $dateTo);
                case 'profit-loss':
                    return $this->exportProfitLoss($dateFrom, $dateTo);
                case 'balance-sheet':
                    return $this->exportBalanceSheet($request->as_of_date ?? Carbon::now()->format('Y-m-d'));
                case 'sales-analytics':
                    return $this->exportSalesAnalytics($request);
                case 'inventory':
                    return $this->exportInventory($request);
                case 'customers':
                    return $this->exportCustomerAnalytics($request);
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid report type'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Export failed'], 500);
        }
    }

    private function exportLedger($dateFrom, $dateTo)
    {
        $data = $this->getLedgerData($dateFrom, $dateTo)->getData(true);

        $filename = "ledger_{$dateFrom}_to_{$dateTo}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, ['Date', 'Description', 'Reference', 'Debit', 'Credit', 'Balance', 'Customer']);

            // Data
            foreach ($data['data']['transactions'] as $transaction) {
                fputcsv($file, [
                    $transaction['date'],
                    $transaction['description'],
                    $transaction['reference'],
                    $transaction['debit'],
                    $transaction['credit'],
                    $transaction['balance'],
                    $transaction['customer'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportProfitLoss($dateFrom, $dateTo)
    {
        // Implementation similar to exportLedger
        $data = $this->getProfitLossData($dateFrom, $dateTo)->getData(true);

        $filename = "profit_loss_{$dateFrom}_to_{$dateTo}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Account', 'Amount']);
            fputcsv($file, ['Revenue', '']);
            fputcsv($file, ['Sales Revenue', $data['data']['revenue']['sales_revenue']]);
            fputcsv($file, ['Less: Refunds', -$data['data']['revenue']['refunds']]);
            fputcsv($file, ['Net Revenue', $data['data']['revenue']['net_revenue']]);
            fputcsv($file, ['', '']);
            fputcsv($file, ['Cost of Goods Sold', -$data['data']['cogs']]);
            fputcsv($file, ['Gross Profit', $data['data']['gross_profit']]);
            fputcsv($file, ['', '']);
            fputcsv($file, ['Operating Expenses', '']);
            foreach ($data['data']['expenses'] as $key => $value) {
                if ($key !== 'total_expenses') {
                    fputcsv($file, [ucwords(str_replace('_', ' ', $key)), -$value]);
                }
            }
            fputcsv($file, ['Total Expenses', -$data['data']['expenses']['total_expenses']]);
            fputcsv($file, ['', '']);
            fputcsv($file, ['Net Profit', $data['data']['net_profit']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportBalanceSheet($asOfDate)
    {
        // Implementation for balance sheet export
        $data = $this->getBalanceSheetData($asOfDate)->getData(true);

        $filename = "balance_sheet_{$asOfDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Balance Sheet', 'Amount']);
            fputcsv($file, ['ASSETS', '']);
            fputcsv($file, ['Current Assets:', '']);
            foreach ($data['data']['assets']['current_assets'] as $key => $value) {
                if ($key !== 'total') {
                    fputcsv($file, ['  ' . ucwords(str_replace('_', ' ', $key)), $value]);
                }
            }
            fputcsv($file, ['Total Assets', $data['data']['assets']['total_assets']]);
            fputcsv($file, ['', '']);
            fputcsv($file, ['LIABILITIES & EQUITY', '']);
            fputcsv($file, ['Total Equity', $data['data']['equity']['total_equity']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export sales analytics to CSV
     */
    private function exportSalesAnalytics(Request $request): StreamedResponse
    {
        $data = $this->salesAnalytics($request)->getData(true);

        $filename = "sales_analytics_{$request->date_from}_to_{$request->date_to}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Sales by Period
            fputcsv($file, ['Sales by Period']);
            fputcsv($file, ['Period', 'Orders Count', 'Total Sales']);
            foreach ($data['data']['sales_by_period'] as $item) {
                fputcsv($file, [$item['period'], $item['orders_count'], $item['total_sales']]);
            }

            fputcsv($file, ['']); // Empty row

            // Top Products
            fputcsv($file, ['Top Products']);
            fputcsv($file, ['Product Name', 'Total Sold', 'Total Revenue']);
            foreach ($data['data']['top_products'] as $product) {
                fputcsv($file, [$product['name'], $product['total_sold'], $product['total_revenue']]);
            }

            fputcsv($file, ['']); // Empty row

            // Sales by Category
            fputcsv($file, ['Sales by Category']);
            fputcsv($file, ['Category', 'Total Sold', 'Total Revenue']);
            foreach ($data['data']['sales_by_category'] as $category) {
                fputcsv($file, [$category['name'], $category['total_sold'], $category['total_revenue']]);
            }

            fputcsv($file, ['']); // Empty row

            // Payment Methods
            fputcsv($file, ['Payment Methods']);
            fputcsv($file, ['Method', 'Transaction Count', 'Total Amount']);
            foreach ($data['data']['payment_methods'] as $method) {
                fputcsv($file, [$method['payment_method'], $method['transaction_count'], $method['total_amount']]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export inventory report to CSV
     */
    private function exportInventory(Request $request): StreamedResponse
    {
        $data = $this->inventoryReport($request)->getData(true);

        $filename = "inventory_report_" . date('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Summary
            fputcsv($file, ['Inventory Summary']);
            fputcsv($file, ['Total Products', $data['data']['summary']['total_products']]);
            fputcsv($file, ['Total Inventory Value', $data['data']['summary']['total_inventory_value']]);
            fputcsv($file, ['Low Stock Items', $data['data']['summary']['low_stock_items']]);
            fputcsv($file, ['Out of Stock Items', $data['data']['summary']['out_of_stock_items']]);

            fputcsv($file, ['']); // Empty row

            // Products
            fputcsv($file, ['Product Details']);
            fputcsv($file, ['Name', 'SKU', 'Category', 'Stock Quantity', 'Min Stock Level', 'Stock Status', 'Cost Price', 'Selling Price', 'Inventory Value']);
            foreach ($data['data']['products'] as $product) {
                fputcsv($file, [
                    $product['name'],
                    $product['sku'] ?? 'N/A',
                    $product['category']['name'] ?? 'N/A',
                    $product['stock_quantity'],
                    $product['min_stock_level'],
                    $product['stock_status'],
                    $product['cost_price'],
                    $product['price'],
                    $product['inventory_value']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export customer analytics to CSV
     */
    private function exportCustomerAnalytics(Request $request): StreamedResponse
    {
        $data = $this->customerAnalytics($request)->getData(true);

        $filename = "customer_analytics_{$request->date_from}_to_{$request->date_to}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Top Customers
            fputcsv($file, ['Top Customers']);
            fputcsv($file, ['Name', 'Email', 'Total Orders', 'Total Spent']);
            foreach ($data['data']['top_customers'] as $customer) {
                fputcsv($file, [$customer['name'], $customer['email'], $customer['total_orders'], $customer['total_spent']]);
            }

            fputcsv($file, ['']); // Empty row

            // New Customers
            fputcsv($file, ['New Customer Acquisition']);
            fputcsv($file, ['Period', 'New Customers']);
            foreach ($data['data']['new_customers'] as $item) {
                fputcsv($file, [$item['period'], $item['new_customers']]);
            }

            fputcsv($file, ['']); // Empty row

            // Payment Type Stats
            fputcsv($file, ['Payment Type Preferences']);
            fputcsv($file, ['Payment Type', 'Order Count', 'Total Amount']);
            foreach ($data['data']['payment_type_stats'] as $stat) {
                fputcsv($file, [$stat['payment_type'], $stat['order_count'], $stat['total_amount']]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
