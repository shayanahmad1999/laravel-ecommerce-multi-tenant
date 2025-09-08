<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default tenant
        $tenant = Tenant::create([
            'name' => 'Demo E-commerce Store',
            'domain' => 'localhost',
            'database' => config('database.connections.mysql.database'),
            'is_active' => true,
        ]);
        
        // Set current tenant
        $tenant->makeCurrent();
        
        // Create roles and permissions
        $this->createRolesAndPermissions();
        
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password123'),
            'user_type' => 'admin',
            'tenant_id' => $tenant->id,
            'phone' => '+1234567890',
            'address' => '123 Admin Street, City, State, 12345',
        ]);
        
        // Create sample customers
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john@demo.com',
                'password' => Hash::make('password123'),
                'user_type' => 'customer',
                'tenant_id' => $tenant->id,
                'phone' => '+1987654321',
                'address' => '456 Customer Ave, City, State, 67890',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@demo.com',
                'password' => Hash::make('password123'),
                'user_type' => 'customer',
                'tenant_id' => $tenant->id,
                'phone' => '+1122334455',
                'address' => '789 Buyer Blvd, City, State, 11223',
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike@demo.com',
                'password' => Hash::make('password123'),
                'user_type' => 'customer',
                'tenant_id' => $tenant->id,
                'phone' => '+1555666777',
                'address' => '321 Shopper St, City, State, 44556',
            ],
        ];
        
        foreach ($customers as $customerData) {
            $customer = User::create($customerData);
            $customer->assignRole('customer');
        }
        
        // Assign admin role
        $admin->assignRole('admin');
        
        $this->command->info('Tenant and users created successfully!');
    }
    
    private function createRolesAndPermissions()
    {
        // Create permissions
        $permissions = [
            'manage categories',
            'manage products',
            'manage orders',
            'manage payments',
            'view reports',
            'manage users',
        ];
        
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $customerRole = Role::create(['name' => 'customer']);
        
        // Assign all permissions to admin
        $adminRole->givePermissionTo($permissions);
        
        // Assign limited permissions to customer
        $customerRole->givePermissionTo(['manage orders']); // Customers can manage their own orders
        
        $this->command->info('Roles and permissions created successfully!');
    }
}
