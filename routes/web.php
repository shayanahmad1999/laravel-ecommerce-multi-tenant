<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\Admin\RolePermissionController as AdminRolePermissionController;
use App\Http\Controllers\DashboardController;

// Public routes (within tenant context for product display)
Route::middleware(['tenant'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');
});

// Authentication routes
Auth::routes();

// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/admin-stats', [DashboardController::class, 'adminStats'])->name('dashboard.admin-stats');
    Route::get('/dashboard/customer-stats', [DashboardController::class, 'customerStats'])->name('dashboard.customer-stats');
    Route::get('/dashboard/recent-orders', [DashboardController::class, 'recentOrders'])->name('dashboard.recent-orders');
    Route::get('/dashboard/low-stock', [DashboardController::class, 'lowStock'])->name('dashboard.low-stock');
    Route::get('/dashboard/sales-analytics', [DashboardController::class, 'salesAnalytics'])->name('dashboard.sales-analytics');
    Route::get('/dashboard/pending-installments', [DashboardController::class, 'pendingInstallments'])->name('dashboard.pending-installments');
});


// Tenant routes
Route::middleware(['tenant'])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth'])->name('dashboard');

    // Categories routes
    Route::controller(CategoryController::class)->prefix('categories')->name('categories.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create')->middleware('role:admin');
        Route::post('/', 'store')->name('store')->middleware('role:admin');
        Route::get('/select', 'getForSelect')->name('select');
        Route::get('/{category}', 'show')->name('show');
        Route::get('/{category}/edit', 'edit')->name('edit')->middleware('role:admin');
        Route::put('/{category}', 'update')->name('update')->middleware('role:admin');
        Route::delete('/{category}', 'destroy')->name('destroy')->middleware('role:admin');
    });

    // Products routes
    Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create')->middleware('role:admin');
        Route::post('/', 'store')->name('store')->middleware('role:admin');
        Route::get('/select', 'getForSelect')->name('select');
        Route::get('/{product}', 'show')->name('show');
        Route::get('/{product}/edit', 'edit')->name('edit')->middleware('role:admin');
        Route::put('/{product}', 'update')->name('update')->middleware('role:admin');
        Route::put('/{product}/stock', 'updateStock')->name('update-stock')->middleware('role:admin');
        Route::delete('/{product}', 'destroy')->name('destroy')->middleware('role:admin');
    });


    // Orders routes
    Route::controller(OrderController::class)->prefix('orders')->name('orders.')->middleware('auth')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/statistics', 'getStatistics')->name('statistics');
        Route::get('/cart', 'getCart')->name('cart');
        Route::post('/add-to-cart', 'addToCart')->name('add-to-cart');
        Route::post('/update-cart', 'updateCartItem')->name('update-cart');
        Route::post('/clear-cart', 'clearCart')->name('clear-cart');
        Route::post('/checkout', 'checkout')->name('checkout');
        Route::get('/{order}', 'show')->name('show');
        Route::put('/{order}/status', 'updateStatus')->name('update-status')->middleware('role:admin');
    });

    // Payments routes
    Route::controller(PaymentController::class)->prefix('payments')->name('payments.')->middleware('auth')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/methods', 'getPaymentMethods')->name('methods');
        Route::get('/pending-installments', 'getPendingInstallments')->name('pending-installments');
        Route::get('/statistics', 'getStatistics')->name('statistics');
        Route::post('/instant', 'processInstantPayment')->name('process-instant');
        Route::post('/installment', 'processInstallmentPayment')->name('process-installment');
        Route::get('/order/{order}', 'showPaymentForm')->name('form');
        Route::get('/installment/{installment}', 'showInstallmentForm')->name('installment-form');
        Route::post('/{payment}/refund', 'processRefund')->name('refund')->middleware('role:admin');
        Route::get('/{payment}', 'show')->name('show');
    });

    // Reports routes
    Route::controller(ReportController::class)->prefix('reports')->name('reports.')->middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/sales', 'sales')->name('sales');
        Route::get('/inventory', 'inventory')->name('inventory');
        Route::get('/orders', 'orders')->name('orders');
        Route::get('/customers', 'customers')->name('customers');
        Route::get('/product-performance', 'productPerformance')->name('product-performance');
        Route::get('/category-performance', 'categoryPerformance')->name('category-performance');
        Route::get('/payment-methods', 'paymentMethods')->name('payment-methods');
        Route::get('/installments', 'installments')->name('installments');
        Route::get('/low-stock', 'lowStock')->name('low-stock');
        Route::get('/revenue-trends', 'revenueTrends')->name('revenue-trends');
        Route::get('/export-pdf', 'exportPdf')->name('export-pdf');
    });
});

// Public API routes for frontend (within tenant context)
Route::middleware(['tenant'])->prefix('api')->group(function () {
    Route::get('/products', function () {
        $products = \App\Models\Product::with('category')
            ->active()
            ->inStock()
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'next_page_url' => $products->nextPageUrl(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
        ]);
    });

    Route::get('/products/{product}', function (\App\Models\Product $product) {
        $product->load('category');
        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    });

    Route::get('/categories', function () {
        $categories = \App\Models\Category::active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    });
});

// Remove duplicate route registrations below; keep a single home route if needed
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard.home');

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    // Tenants
    Route::get('/tenants', [AdminTenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/create', [AdminTenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [AdminTenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants/{tenant}/edit', [AdminTenantController::class, 'edit'])->name('tenants.edit');
    Route::put('/tenants/{tenant}', [AdminTenantController::class, 'update'])->name('tenants.update');
    Route::delete('/tenants/{tenant}', [AdminTenantController::class, 'destroy'])->name('tenants.destroy');
    Route::put('/tenants/{tenant}/toggle', [AdminTenantController::class, 'toggleActive'])->name('tenants.toggle');

    // Users CRUD
    Route::get('/users', [AdminRolePermissionController::class, 'users'])->name('users.index');
    Route::get('/users/search', [AdminRolePermissionController::class, 'searchCustomers'])->name('users.search');
    Route::get('/users/create', [AdminRolePermissionController::class, 'createUserForm'])->name('users.create');
    Route::post('/users', [AdminRolePermissionController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminRolePermissionController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminRolePermissionController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminRolePermissionController::class, 'destroyUser'])->name('users.destroy');
    Route::put('/users/{user}/assign-role', [AdminRolePermissionController::class, 'assignUserRole'])->name('users.assign-role');



    // Roles CRUD
    Route::get('/roles', [AdminRolePermissionController::class, 'roles'])->name('roles.index');
    Route::get('/roles/create', [AdminRolePermissionController::class, 'createRoleForm'])->name('roles.create');
    Route::post('/roles', [AdminRolePermissionController::class, 'storeRole'])->name('roles.store');
    Route::get('/roles/{role}/edit', [AdminRolePermissionController::class, 'editRole'])->name('roles.edit');
    Route::put('/roles/{role}', [AdminRolePermissionController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{role}', [AdminRolePermissionController::class, 'destroyRole'])->name('roles.destroy');
    Route::put('/roles/{role}/permissions', [AdminRolePermissionController::class, 'syncRolePermissions'])->name('roles.sync-permissions');

    // Permissions CRUD
    Route::get('/permissions', [AdminRolePermissionController::class, 'permissions'])->name('permissions.index');
    Route::get('/permissions/create', [AdminRolePermissionController::class, 'createPermissionForm'])->name('permissions.create');
    Route::post('/permissions', [AdminRolePermissionController::class, 'storePermission'])->name('permissions.store');
    Route::get('/permissions/{permission}/edit', [AdminRolePermissionController::class, 'editPermission'])->name('permissions.edit');
    Route::put('/permissions/{permission}', [AdminRolePermissionController::class, 'updatePermission'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [AdminRolePermissionController::class, 'destroyPermission'])->name('permissions.destroy');
});
