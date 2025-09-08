<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Payment Seeder
 *
 * Creates sample payment data for testing and demonstration purposes.
 */
class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample users if they don't exist
        $users = User::all();
        if ($users->isEmpty()) {
            $users = collect([
                User::create([
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'password' => bcrypt('password'),
                    'user_type' => 'customer',
                ]),
                User::create([
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com',
                    'password' => bcrypt('password'),
                    'user_type' => 'customer',
                ]),
                User::create([
                    'name' => 'Bob Johnson',
                    'email' => 'bob@example.com',
                    'password' => bcrypt('password'),
                    'user_type' => 'customer',
                ]),
            ]);
        }

        // Create sample categories if they don't exist
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $categories = collect([
                Category::create(['name' => 'Electronics', 'description' => 'Electronic devices and gadgets']),
                Category::create(['name' => 'Clothing', 'description' => 'Fashion and apparel']),
                Category::create(['name' => 'Books', 'description' => 'Books and publications']),
            ]);
        }

        // Create sample products if they don't exist
        $products = Product::all();
        if ($products->isEmpty()) {
            $products = collect([
                Product::create([
                    'name' => 'iPhone 15 Pro',
                    'description' => 'Latest iPhone with advanced features',
                    'sku' => 'IPH15P-128',
                    'price' => 999.99,
                    'cost_price' => 800.00,
                    'stock_quantity' => 50,
                    'min_stock_level' => 10,
                    'category_id' => $categories->first()->id,
                    'is_active' => true,
                ]),
                Product::create([
                    'name' => 'MacBook Pro 16"',
                    'description' => 'Powerful laptop for professionals',
                    'sku' => 'MBP16-M3',
                    'price' => 2499.99,
                    'cost_price' => 2000.00,
                    'stock_quantity' => 25,
                    'min_stock_level' => 5,
                    'category_id' => $categories->first()->id,
                    'is_active' => true,
                ]),
                Product::create([
                    'name' => 'Designer T-Shirt',
                    'description' => 'Comfortable cotton t-shirt',
                    'sku' => 'TSHIRT-BLK-L',
                    'price' => 29.99,
                    'cost_price' => 15.00,
                    'stock_quantity' => 100,
                    'min_stock_level' => 20,
                    'category_id' => $categories->skip(1)->first()->id,
                    'is_active' => true,
                ]),
                Product::create([
                    'name' => 'Programming Book',
                    'description' => 'Learn Laravel development',
                    'sku' => 'BOOK-LARAVEL',
                    'price' => 49.99,
                    'cost_price' => 25.00,
                    'stock_quantity' => 75,
                    'min_stock_level' => 15,
                    'category_id' => $categories->skip(2)->first()->id,
                    'is_active' => true,
                ]),
            ]);
        }

        // Create sample orders and payments
        $this->createSampleOrdersAndPayments($users, $products);
    }

    /**
     * Create sample orders and payments.
     */
    private function createSampleOrdersAndPayments($users, $products): void
    {
        $paymentMethods = ['credit_card', 'debit_card', 'bank_transfer', 'cash', 'digital_wallet'];
        $statuses = ['pending', 'processing', 'completed', 'failed', 'refunded'];

        // Create 20 sample orders with payments
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $orderDate = Carbon::now()->subDays(rand(0, 90));

            DB::transaction(function () use ($user, $products, $paymentMethods, $statuses, $orderDate, $i) {
                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => collect(['pending', 'processing', 'shipped', 'delivered'])->random(),
                    'subtotal' => 0, // Will be calculated
                    'tax_amount' => 0,
                    'shipping_cost' => rand(0, 50),
                    'discount_amount' => 0,
                    'total_amount' => 0, // Will be calculated
                    'payment_type' => collect(['instant', 'installment'])->random(),
                    'shipping_address' => [
                        'street' => fake()->streetAddress(),
                        'city' => fake()->city(),
                        'state' => fake()->state(),
                        'zip' => fake()->postcode(),
                        'country' => 'USA',
                    ],
                    'billing_address' => [
                        'street' => fake()->streetAddress(),
                        'city' => fake()->city(),
                        'state' => fake()->state(),
                        'zip' => fake()->postcode(),
                        'country' => 'USA',
                    ],
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                // Create order items
                $orderItems = [];
                $subtotal = 0;
                $numItems = rand(1, 4);

                for ($j = 0; $j < $numItems; $j++) {
                    $product = $products->random();
                    $quantity = rand(1, 3);
                    $unitPrice = $product->price;
                    $totalPrice = $quantity * $unitPrice;

                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);

                    $orderItems[] = $orderItem;
                    $subtotal += $totalPrice;

                    // Update product stock
                    $product->decrement('stock_quantity', $quantity);
                }

                // Calculate totals
                $taxAmount = $subtotal * 0.08; // 8% tax
                $totalAmount = $subtotal + $taxAmount + $order->shipping_cost;

                // Update order with calculated totals
                $order->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                ]);

                // Create payment
                $paymentMethod = collect($paymentMethods)->random();
                $paymentStatus = collect($statuses)->random();
                $paymentAmount = $totalAmount;

                // For some payments, make them partial or installment
                if (rand(1, 10) <= 3) { // 30% chance
                    $paymentAmount = $totalAmount * (rand(50, 90) / 100); // 50-90% of total
                }

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'amount' => $paymentAmount,
                    'payment_method' => $paymentMethod,
                    'payment_type' => $paymentAmount >= $totalAmount ? 'full' : 'installment',
                    'status' => $paymentStatus,
                    'transaction_id' => $paymentStatus === 'completed' ? 'TXN_' . strtoupper(uniqid()) : null,
                    'gateway_response' => $paymentStatus === 'completed' ? 'Payment processed successfully' : 'Payment failed',
                    'payment_details' => [
                        'method' => $paymentMethod,
                        'amount' => $paymentAmount,
                        'currency' => 'USD',
                        'processed_at' => $paymentStatus === 'completed' ? $orderDate->toISOString() : null,
                    ],
                    'processed_at' => $paymentStatus === 'completed' ? $orderDate : null,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                // Create installment if payment type is installment
                if ($order->payment_type === 'installment' && $paymentStatus === 'completed') {
                    $this->createInstallmentsForOrder($order, $payment);
                }

                // Update order status based on payment
                if ($paymentStatus === 'completed' && $paymentAmount >= $totalAmount) {
                    $order->update(['status' => 'processing']);
                }
            });
        }

        $this->command->info('Created 20 sample orders with payments');
    }

    /**
     * Create installments for an order.
     */
    private function createInstallmentsForOrder(Order $order, Payment $payment): void
    {
        $totalAmount = $order->total_amount;
        $numInstallments = rand(3, 12); // 3 to 12 installments
        $installmentAmount = round($totalAmount / $numInstallments, 2);
        $dueDate = Carbon::now()->addMonth();

        for ($i = 1; $i <= $numInstallments; $i++) {
            $installment = \App\Models\Installment::create([
                'order_id' => $order->id,
                'installment_number' => $i,
                'amount' => $installmentAmount,
                'interest_amount' => 0,
                'total_amount' => $installmentAmount,
                'status' => $i === 1 ? 'paid' : 'pending', // First installment paid
                'due_date' => $dueDate,
                'payment_id' => $i === 1 ? $payment->id : null, // Link first installment to payment
                'paid_at' => $i === 1 ? Carbon::now() : null,
            ]);

            $dueDate = $dueDate->addMonth();
        }
    }
}