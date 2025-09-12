<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AssignCustomerRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-customer-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign customer roles to users with user_type = customer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Assigning customer roles to existing customers...');

        $customers = \App\Models\User::where('user_type', 'customer')
            ->whereDoesntHave('roles')
            ->get();

        if ($customers->isEmpty()) {
            $this->info('No customers found without roles.');
            return;
        }

        $this->info("Found {$customers->count()} customers without roles.");

        $bar = $this->output->createProgressBar($customers->count());

        foreach ($customers as $customer) {
            $customer->assignRole('customer');
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Customer roles assigned successfully!');
    }
}
