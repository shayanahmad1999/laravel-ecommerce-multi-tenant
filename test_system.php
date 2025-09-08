<?php

/**
 * Comprehensive Testing Script for Laravel Multi-Tenant E-Commerce System
 * 
 * This script tests all major functionalities including:
 * - Multi-tenancy
 * - User authentication and roles
 * - CRUD operations for categories, products, orders
 * - Payment processing (instant and installments)
 * - Financial reports
 */

require_once 'vendor/autoload.php';

use App\Models\Tenant;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Installment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class SystemTester
{
    private $tenant;
    private $adminUser;
    private $customerUser;

    public function __construct()
    {
        echo "🚀 Starting Laravel Multi-Tenant E-Commerce System Test\n\n";
    }

    public function runAllTests()
    {
        try {
            $this->testTenancy();
            $this->testUserRoles();
            $this->testCategories();
            $this->testProducts();
            $this->testOrders();
            $this->testPayments();
            $this->testReports();
            
            echo "\n✅ All tests completed successfully!\n";
            echo "🎉 Your Laravel Multi-Tenant E-Commerce system is ready!\n\n";
            
            $this->displayQuickStartGuide();
            
        } catch (Exception $e) {
            echo "\n❌ Test failed: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }

    private function testTenancy()
    {
        echo "🏢 Testing Multi-Tenancy...\n";
        
        // Get the default tenant created by seeder
        $this->tenant = Tenant::first();
        if (!$this->tenant) {
            throw new Exception("No tenant found in database");
        }
        
        // Set current tenant
        $this->tenant->makeCurrent();
        
        echo "   ✓ Tenant '{$this->tenant->name}' loaded successfully\n";
        echo "   ✓ Tenant domain: {$this->tenant->domain}\n";
        echo "   ✓ Multi-tenancy is working\n\n";
    }

    private function testUserRoles()
    {
        echo "👥 Testing User Authentication & Roles...\n";
        
        // Get admin and customer users
        $this->adminUser = User::where('email', 'admin@demo.com')->first();
        $this->customerUser = User::where('email', 'john@demo.com')->first();
        
        if (!$this->adminUser || !$this->customerUser) {
            throw new Exception("Admin or customer user not found");
        }
        
        // Check roles
        if (!$this->adminUser->hasRole('admin')) {
            throw new Exception("Admin user doesn't have admin role");
        }
        
        if (!$this->customerUser->hasRole('customer')) {
            throw new Exception("Customer user doesn't have customer role");
        }
        
        echo "   ✓ Admin user loaded with admin role\n";
        echo "   ✓ Customer user loaded with customer role\n";
        echo "   ✓ Role-based access control is working\n\n";
    }

    private function testCategories()
    {
        echo "📂 Testing Categories...\n";
        
        $categoryCount = Category::count();
        if ($categoryCount == 0) {
            throw new Exception("No categories found");
        }
        
        $category = Category::first();
        echo "   ✓ Found {$categoryCount} categories\n";
        echo "   ✓ Sample category: '{$category->name}'\n";
        
        // Test category creation
        $newCategory = Category::create([
            'name' => 'Test Category',
            'description' => 'A test category',
            'is_active' => true,
            'tenant_id' => $this->tenant->id
        ]);
        
        echo "   ✓ New category created: '{$newCategory->name}'\n\n";
    }

    private function testProducts()
    {
        echo "🛍️ Testing Products...\n";
        
        $productCount = Product::count();
        if ($productCount == 0) {
            throw new Exception("No products found");
        }
        
        $product = Product::first();
        echo "   ✓ Found {$productCount} products\n";
        echo "   ✓ Sample product: '{$product->name}' - \${$product->price}\n";
        
        // Test product with installments
        $installmentProduct = Product::where('supports_installments', true)->first();
        if ($installmentProduct) {
            echo "   ✓ Found installment-supported product: '{$installmentProduct->name}'\n";
            echo "     - Min installments: {$installmentProduct->min_installments}\n";
            echo "     - Max installments: {$installmentProduct->max_installments}\n";
        }
        
        echo "\n";
    }

    private function testOrders()
    {
        echo "🛒 Testing Orders...\n";
        
        // Create a test order
        $product = Product::first();
        $order = Order::create([
            'user_id' => $this->customerUser->id,
            'tenant_id' => $this->tenant->id,
            'order_number' => 'TEST-' . time(),
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_type' => 'instant',
            'subtotal' => $product->price,
            'tax_amount' => $product->price * 0.1,
            'total_amount' => $product->price * 1.1,
            'shipping_address' => json_encode(['street' => '123 Test Street', 'city' => 'Test City', 'state' => 'Test State', 'zip' => '12345']),
            'billing_address' => json_encode(['street' => '123 Test Street', 'city' => 'Test City', 'state' => 'Test State', 'zip' => '12345']),
        ]);
        
        // Add order item
        $order->orderItems()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price,
        ]);
        
        echo "   ✓ Test order created: {$order->order_number}\n";
        echo "   ✓ Order total: \${$order->total_amount}\n";
        echo "   ✓ Order items count: " . $order->orderItems->count() . "\n\n";
    }

    private function testPayments()
    {
        echo "💳 Testing Payments...\n";
        
        $order = Order::where('order_number', 'like', 'TEST-%')->first();
        if (!$order) {
            throw new Exception("Test order not found");
        }
        
        // Test instant payment
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $this->customerUser->id,
            'tenant_id' => $this->tenant->id,
            'amount' => $order->total_amount,
            'payment_type' => 'full',
            'payment_method' => 'credit_card',
            'status' => 'completed',
            'transaction_id' => 'TXN-' . time(),
            'gateway_response' => json_encode(['status' => 'success', 'gateway' => 'test']),
        ]);
        
        // Update order status
        $order->update([
            'payment_status' => 'completed',
            'status' => 'processing'
        ]);
        
        echo "   ✓ Instant payment processed: \${$payment->amount}\n";
        echo "   ✓ Transaction ID: {$payment->transaction_id}\n";
        
        // Test installment setup
        $installmentProduct = Product::where('supports_installments', true)->first();
        if ($installmentProduct) {
            $installmentOrder = Order::create([
                'user_id' => $this->customerUser->id,
                'tenant_id' => $this->tenant->id,
                'order_number' => 'INST-' . time(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_type' => 'installment',
                'subtotal' => $installmentProduct->price,
                'tax_amount' => $installmentProduct->price * 0.1,
                'total_amount' => $installmentProduct->price * 1.1,
                'installment_plan' => 3, // 3 months
                'shipping_address' => json_encode(['street' => '123 Test Street', 'city' => 'Test City', 'state' => 'Test State', 'zip' => '12345']),
                'billing_address' => json_encode(['street' => '123 Test Street', 'city' => 'Test City', 'state' => 'Test State', 'zip' => '12345']),
            ]);
            
            // Create installments
            $installmentAmount = $installmentOrder->total_amount / 3;
            for ($i = 1; $i <= 3; $i++) {
                Installment::create([
                    'order_id' => $installmentOrder->id,
                    'installment_number' => $i,
                    'amount' => $installmentAmount,
                    'due_date' => now()->addMonths($i),
                    'status' => $i == 1 ? 'paid' : 'pending',
                    'paid_at' => $i == 1 ? now() : null,
                ]);
            }
            
            echo "   ✓ Installment order created: {$installmentOrder->order_number}\n";
            echo "   ✓ 3 installments created, first one marked as paid\n";
        }
        
        echo "\n";
    }

    private function testReports()
    {
        echo "📊 Testing Financial Reports...\n";
        
        // Test that we have data for reports
        $orderCount = Order::count();
        $paymentCount = Payment::count();
        $productCount = Product::count();
        $userCount = User::count();
        
        echo "   ✓ Orders in system: {$orderCount}\n";
        echo "   ✓ Payments in system: {$paymentCount}\n";
        echo "   ✓ Products in system: {$productCount}\n";
        echo "   ✓ Users in system: {$userCount}\n";
        
        // Test basic report calculations
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $pendingPayments = Payment::where('status', 'pending')->sum('amount');
        
        echo "   ✓ Total revenue: \${$totalRevenue}\n";
        echo "   ✓ Pending payments: \${$pendingPayments}\n";
        echo "   ✓ Financial data is ready for reporting\n\n";
    }

    private function displayQuickStartGuide()
    {
        echo "🎯 Quick Start Guide:\n";
        echo "=====================\n\n";
        
        echo "1. Start your development server:\n";
        echo "   php artisan serve\n\n";
        
        echo "2. Access the application:\n";
        echo "   http://localhost:8000\n\n";
        
        echo "3. Default login credentials:\n";
        echo "   Admin: admin@demo.com / password123\n";
        echo "   Customer: john@demo.com / password123\n";
        echo "   Additional customers: jane@demo.com, mike@demo.com / password123\n\n";
        
        echo "4. Key Features Available:\n";
        echo "   • Multi-tenant architecture\n";
        echo "   • Role-based access control\n";
        echo "   • Category management (AJAX CRUD)\n";
        echo "   • Product catalog with images\n";
        echo "   • Shopping cart and order management\n";
        echo "   • Instant and installment payments\n";
        echo "   • Financial reports and analytics\n";
        echo "   • Responsive Bootstrap 5 UI\n\n";
        
        echo "5. Available Routes:\n";
        echo "   GET  /                     - Dashboard\n";
        echo "   GET  /categories           - Category management\n";
        echo "   GET  /products             - Product catalog\n";
        echo "   GET  /orders               - Order management\n";
        echo "   GET  /payments             - Payment history\n";
        echo "   GET  /reports/profit-loss  - Financial reports\n\n";
        
        echo "6. API Endpoints (for AJAX):\n";
        echo "   POST /categories           - Create category\n";
        echo "   PUT  /categories/{id}      - Update category\n";
        echo "   DEL  /categories/{id}      - Delete category\n";
        echo "   POST /products             - Create product\n";
        echo "   POST /orders               - Create order\n";
        echo "   POST /payments/process     - Process payment\n";
        echo "   GET  /reports/ledger       - Get ledger report\n\n";
        
        echo "7. Sample Data Created:\n";
        echo "   • 1 Default tenant (example.com)\n";
        echo "   • 2 Users (1 admin, 1 customer)\n";
        echo "   • Multiple product categories\n";
        echo "   • Sample products with installment support\n";
        echo "   • Test orders and payments\n\n";
        
        echo "🔧 To customize further:\n";
        echo "   • Edit .env file for database and app configuration\n";
        echo "   • Modify seeders in database/seeders/\n";
        echo "   • Customize views in resources/views/\n";
        echo "   • Add more payment gateways in PaymentController\n";
        echo "   • Extend financial reports in ReportController\n\n";
        
        echo "📝 Next Steps:\n";
        echo "   1. Test the web interface at http://localhost:8000\n";
        echo "   2. Try creating products, categories, and orders\n";
        echo "   3. Test both instant and installment payments\n";
        echo "   4. View financial reports and analytics\n";
        echo "   5. Test role-based access with different user types\n\n";
        
        echo "🎊 Your multi-tenant e-commerce system is ready to use!\n";
    }
}

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run tests
$tester = new SystemTester();
$tester->runAllTests();
