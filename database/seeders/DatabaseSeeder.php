<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
        ]);

        User::factory()->create([
            'name' => 'ibrahim',
            'email' => 'ibrahim@budget.com',
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
        ]);
    }
}
