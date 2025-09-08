<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Installment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function adminStats()
    {
        // $this->authorize('viewAdminDashboard'); // Gate/Policy
        return response()->json([
            'success' => true,
            'data' => [
                'total_orders'   => Order::count(),
                'total_revenue'  => (float) Order::sum('total_amount'),
                'growth_orders'  => $this->growthPercent(Order::class, 'created_at'),
                'growth_revenue' => $this->growthSumPercent(Order::class, 'total_amount', 'created_at'),
            ],
        ]);
    }

    public function customerStats()
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'data' => [
                'total_orders'  => $user->orders()->count(),
                'total_revenue' => (float) $user->orders()->sum('total_amount'),
            ],
        ]);
    }

    public function recentOrders(Request $req)
    {
        $limit = (int) $req->integer('limit', 5);
        $query = Order::with(['user:id,name'])
            ->latest('created_at');

        // If not admin, restrict to current user's orders
        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        return response()->json([
            'success' => true,
            'data' => $query->take($limit)->get(['id', 'order_number', 'total_amount', 'status', 'created_at', 'user_id']),
        ]);
    }

    public function lowStock()
    {
        // $this->authorize('viewAdminDashboard');
        $LOW = 5; // centralize threshold
        $products = Product::query()
            ->select('id', 'name', 'sku', 'stock_quantity')
            ->where('stock_quantity', '<=', $LOW)
            ->orderBy('stock_quantity')
            ->take(20)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'stock_quantity' => $p->stock_quantity,
                'stock_status' => 'low',
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_products'  => Product::count(),
                    'low_stock_items' => Product::where('stock_quantity', '<=', $LOW)->count(),
                ],
                'products' => $products,
            ],
        ]);
    }

    public function salesAnalytics()
    {
        // $this->authorize('viewAdminDashboard');

        // Example last-30-days daily buckets
        $from = now()->subDays(29)->startOfDay();
        $to = now()->endOfDay();

        $salesByDay = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as period, SUM(total_amount) as total_sales')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $paymentTypeStats = Order::selectRaw('payment_type, COUNT(*) as order_count')
            ->groupBy('payment_type')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sales_by_period'    => $salesByDay,
                'payment_type_stats' => $paymentTypeStats,
            ],
        ]);
    }

    public function pendingInstallments()
    {
        $user = Auth::user();
        // $this->authorize('viewCustomerInstallments', $user);

        $inst = Installment::with(['order:id,order_number'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date')
            ->get(['id', 'installment_number', 'order_id', 'total_amount', 'due_date', 'status']);

        return response()->json(['success' => true, 'data' => $inst]);
    }

    // Simple helpers (robust versions would handle division by zero, etc.)
    private function growthPercent($modelClass, $dateCol)
    {
        $now = now();
        $curr = $modelClass::whereBetween($dateCol, [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count();
        $prev = $modelClass::whereBetween($dateCol, [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])->count();
        return $prev ? round((($curr - $prev) / $prev) * 100, 1) : 0.0;
    }
    private function growthSumPercent($modelClass, $sumCol, $dateCol)
    {
        $now = now();
        $curr = (float) $modelClass::whereBetween($dateCol, [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->sum($sumCol);
        $prev = (float) $modelClass::whereBetween($dateCol, [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])->sum($sumCol);
        return $prev ? round((($curr - $prev) / $prev) * 100, 1) : 0.0;
    }
}
