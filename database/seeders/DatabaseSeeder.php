<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed all the custom seeders for the multi-tenant e-commerce system
        $this->call([
            TenantSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
