<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed the data a production install needs. Safe to run on every deploy.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
        ]);

        $this->seedAdministrator();
    }

    /**
     * Create the initial administrator from config, if it does not exist yet.
     */
    private function seedAdministrator(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('Skipping admin user: set ADMIN_EMAIL and ADMIN_PASSWORD to create one.');

            return;
        }

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => config('admin.name'),
                'password' => $password,
                'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            ],
        );
    }
}
