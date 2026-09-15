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

        $this->assignDefaultRoles();
        $this->seedAdministrator();
    }

    /**
     * Give users that registered before the roles existed the regular user role.
     */
    private function assignDefaultRoles(): void
    {
        User::whereNull('role_id')->update([
            'role_id' => Role::where('name', Role::USER)->value('id'),
        ]);
    }

    /**
     * Create the initial administrator from config and make sure it has the admin role.
     * An existing account keeps its password.
     */
    private function seedAdministrator(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('Skipping admin user: set ADMIN_EMAIL and ADMIN_PASSWORD to create one.');

            return;
        }

        $adminRoleId = Role::where('name', Role::ADMIN)->value('id');

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => config('admin.name'),
                'password' => $password,
                'role_id' => $adminRoleId,
            ],
        );

        if ($admin->role_id !== $adminRoleId) {
            $admin->update(['role_id' => $adminRoleId]);
        }
    }
}
