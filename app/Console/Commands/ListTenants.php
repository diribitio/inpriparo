<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Multitenancy\Models\Tenant;

class ListTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all tenants.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenants = Tenant::all();

        if (count($tenants) == 0) {
            $this->info('No tenants');
            return;
        }

        $this->info("To execute a command for a tenants database run 'php artisan tenants:artisan \"your command --database=tenant\" --tenant=tenant-id'");

        foreach ($tenants as $tenant) {
            $this->info('');
            $this->info($tenant->name . ' (id=' . $tenant->id . ')');
            $this->info('-----------------------------------------');
            $this->info('Domain ' . $tenant->domain);
            $this->info('Database ' . $tenant->database);
        }
    }
}
