<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Models\Tenant;

class DeleteTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:delete {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes the tenant with the corresponding name.';

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
        $name = $this->argument('name');

        $tenant = $this->tenantExists($name);

        if (!$tenant) {
            $this->error("No tenant with name '{$name}' exists.");
            return;
        }

        if (App::environment('local')) {
            try {
                DB::statement("DROP DATABASE " . $tenant->database);
                $this->info('Successfully deleted the tenants database.');
            } catch (\Illuminate\Database\QueryException $e) {
                $this->error('Failed to delete the tenants database (It probably doesn\'t exist).');
            }
        } else {
            $this->info('The tenants database was not deleted since you are not in the local environment.');
        }

        if ($tenant->delete()) {
            $this->info('Successfully deleted the tenant.');
        } else {
            $this->error('An unknown error occured while deleting the tenant.');
        }
    }

    private function tenantExists($name)
    {
        return Tenant::where('name', $name)->first();
    }
}
