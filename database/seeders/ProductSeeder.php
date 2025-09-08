<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        
        $products = [
            // Electronics - Smartphones
            [
                'category' => 'Smartphones',
                'products' => [
                    [
                        'name' => 'iPhone 15 Pro',
                        'description' => 'Latest iPhone with advanced camera system',
                        'sku' => 'IPH15PRO001',
                        'price' => 999.99,
                        'cost_price' => 700.00,
                        'stock_quantity' => 25,
                        'min_stock_level' => 5,
                        'allow_installments' => true,
                        'max_installments' => 24,
                        'installment_interest_rate' => 5.0,
                    ],
                    [
                        'name' => 'Samsung Galaxy S24',
                        'description' => 'Premium Android smartphone',
                        'sku' => 'SAM24001',
                        'price' => 899.99,
                        'cost_price' => 650.00,
                        'stock_quantity' => 30,
                        'min_stock_level' => 5,
                        'allow_installments' => true,
                        'max_installments' => 18,
                        'installment_interest_rate' => 4.5,
                    ],
                ]
            ],
            // Electronics - Laptops
            [
                'category' => 'Laptops',
                'products' => [
                    [
                        'name' => 'MacBook Pro 16"',
                        'description' => 'High-performance laptop for professionals',
                        'sku' => 'MBP16001',
                        'price' => 2499.99,
                        'cost_price' => 1800.00,
                        'stock_quantity' => 15,
                        'min_stock_level' => 3,
                        'allow_installments' => true,
                        'max_installments' => 36,
                        'installment_interest_rate' => 6.0,
                    ],
                    [
                        'name' => 'Dell XPS 13',
                        'description' => 'Compact and powerful ultrabook',
                        'sku' => 'DELLXPS001',
                        'price' => 1299.99,
                        'cost_price' => 900.00,
                        'stock_quantity' => 20,
                        'min_stock_level' => 4,
                        'allow_installments' => true,
                        'max_installments' => 24,
                        'installment_interest_rate' => 5.5,
                    ],
                ]
            ],
            // Clothing - Men's
            [
                'category' => 'Men\'s Clothing',
                'products' => [
                    [
                        'name' => 'Classic Denim Jeans',
                        'description' => 'Comfortable straight-fit denim jeans',
                        'sku' => 'JEANS001',
                        'price' => 79.99,
                        'cost_price' => 40.00,
                        'stock_quantity' => 100,
                        'min_stock_level' => 20,
                        'allow_installments' => false,
                    ],
                    [
                        'name' => 'Business Casual Shirt',
                        'description' => 'Professional button-down shirt',
                        'sku' => 'SHIRT001',
                        'price' => 49.99,
                        'cost_price' => 25.00,
                        'stock_quantity' => 80,
                        'min_stock_level' => 15,
                        'allow_installments' => false,
                    ],
                ]
            ],
            // Home & Garden - Kitchen
            [
                'category' => 'Kitchen',
                'products' => [
                    [
                        'name' => 'Professional Chef Knife Set',
                        'description' => '8-piece professional kitchen knife set',
                        'sku' => 'KNIFE001',
                        'price' => 299.99,
                        'cost_price' => 150.00,
                        'stock_quantity' => 35,
                        'min_stock_level' => 8,
                        'allow_installments' => true,
                        'max_installments' => 12,
                        'installment_interest_rate' => 3.0,
                    ],
                ]
            ],
            // Sports & Fitness
            [
                'category' => 'Gym Equipment',
                'products' => [
                    [
                        'name' => 'Adjustable Dumbbell Set',
                        'description' => 'Space-saving adjustable dumbbells 5-50lbs',
                        'sku' => 'DUMB001',
                        'price' => 399.99,
                        'cost_price' => 200.00,
                        'stock_quantity' => 12,
                        'min_stock_level' => 3,
                        'allow_installments' => true,
                        'max_installments' => 18,
                        'installment_interest_rate' => 4.0,
                    ],
                ]
            ],
        ];
        
        foreach ($products as $categoryGroup) {
            $category = Category::where('name', $categoryGroup['category'])->first();
            
            if ($category) {
                foreach ($categoryGroup['products'] as $productData) {
                    Product::create(array_merge($productData, [
                        'category_id' => $category->id,
                        'is_active' => true,
                    ]));
                }
            }
        }
        
        $this->command->info('Products created successfully!');
    }
}
