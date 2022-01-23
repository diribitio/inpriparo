<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Models\Tenant;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {name} {subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a tenant and the corresponding database, e.g. php artisan tenant:create MyTenantInc mytenantinc';

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
        $subdomain = $this->argument('subdomain');

        $baseUrl = config('inpriparo.baseUrl');
        $domain = $subdomain . '.' . $baseUrl;

        if ($this->tenantExists($name, $domain)) {
            $this->error("A tenant with name '{$name}' and/or domain '{$domain}' already exists.");
            return;
        }

        $database = 'inpriparo_tenant_' . $name;

        if (DB::statement("CREATE DATABASE " . $database)) {
            $tenant = new Tenant;
            $tenant->name = $name;
            $tenant->domain = $domain;
            $tenant->database = $database;

            if ($tenant->save()) {
                $this->info("Successfully created tenant '{$name}' with database '{$database}'.");
                $this->info("The tenant is now accessible under '{$tenant->domain}'.");
                $this->info("To execute a command for this tenants database run 'php artisan tenants:artisan \"your command --database=tenant\" --tenant={$tenant->id}'");
                $this->info("Before continuing migrate and seed the database!");
            } else {
                $this->error('An unknown error occured while creating the tenant.');
            }
        } else {
            $this->error('Failed to create the database for the tenant.');
        }
    }

    private function tenantExists($name, $domain)
    {
        return Tenant::where('name', $name)->orWhere('domain', $domain)->exists();
    }
}
