<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
                'is_active' => true,
                'sort_order' => 1,
                'subcategories' => [
                    ['name' => 'Smartphones', 'description' => 'Mobile phones and accessories'],
                    ['name' => 'Laptops', 'description' => 'Laptops and computer accessories'],
                    ['name' => 'Tablets', 'description' => 'Tablets and e-readers'],
                ]
            ],
            [
                'name' => 'Clothing',
                'description' => 'Fashion and clothing items',
                'is_active' => true,
                'sort_order' => 2,
                'subcategories' => [
                    ['name' => 'Men\'s Clothing', 'description' => 'Clothing for men'],
                    ['name' => 'Women\'s Clothing', 'description' => 'Clothing for women'],
                    ['name' => 'Accessories', 'description' => 'Fashion accessories'],
                ]
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Home improvement and garden supplies',
                'is_active' => true,
                'sort_order' => 3,
                'subcategories' => [
                    ['name' => 'Furniture', 'description' => 'Home furniture'],
                    ['name' => 'Kitchen', 'description' => 'Kitchen appliances and tools'],
                    ['name' => 'Garden', 'description' => 'Garden tools and plants'],
                ]
            ],
            [
                'name' => 'Sports & Fitness',
                'description' => 'Sports equipment and fitness gear',
                'is_active' => true,
                'sort_order' => 4,
                'subcategories' => [
                    ['name' => 'Gym Equipment', 'description' => 'Exercise and gym equipment'],
                    ['name' => 'Outdoor Sports', 'description' => 'Outdoor sports equipment'],
                    ['name' => 'Activewear', 'description' => 'Sports and fitness clothing'],
                ]
            ],
        ];
        
        foreach ($categories as $categoryData) {
            $subcategories = $categoryData['subcategories'] ?? [];
            unset($categoryData['subcategories']);
            
            $category = Category::create($categoryData);
            
            // Create subcategories
            foreach ($subcategories as $subCategoryData) {
                Category::create(array_merge($subCategoryData, [
                    'parent_id' => $category->id,
                    'is_active' => true,
                    'sort_order' => 0,
                ]));
            }
        }
        
        $this->command->info('Categories created successfully!');
    }
}
