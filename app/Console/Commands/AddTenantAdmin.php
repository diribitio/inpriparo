<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Multitenancy\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Notifications\AdminInvitationNotification;

class AddTenantAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:add_admin {name} {username} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates an admin for the tenant.';

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

        $tenant->makeCurrent();

        $username = $this->argument('username');
        $email = $this->argument('email');
        $password = Str::random(32);

        if ($this->userExists($username, $email)) {
            $this->error("A user with that name or email already exists.");
            return;
        }

        $this->addAdmin($username, $email, $password)->notify(new AdminInvitationNotification());

        $this->info("The tanant admin {$email} has now been invited!");
    }

    private function tenantExists($name)
    {
        return Tenant::where('name', $name)->first();
    }

    private function userExists($name, $email)
    {
        return User::where('name', $name)->orWhere('email', $email)->first();
    }

    private function addAdmin($name, $email, $password)
    {
        $admin = User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
        $admin->assignRole('user');
        $admin->assignRole('admin');

        return $admin;
    }
}
