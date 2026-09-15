<?php

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config([
        'admin.name' => 'Site Admin',
        'admin.email' => 'admin@example.com',
        'admin.password' => 'super-secret-password',
    ]);
});

it('seeds roles, categories and the admin user', function () {
    $this->seed(ProductionSeeder::class);

    expect(Role::pluck('name')->all())->toEqualCanonicalizing([Role::ADMIN, Role::USER])
        ->and(Category::count())->toBe(10);

    $admin = User::where('email', 'admin@example.com')->firstOrFail();

    expect($admin->name)->toBe('Site Admin')
        ->and($admin->isAdmin())->toBeTrue()
        ->and(Hash::check('super-secret-password', $admin->password))->toBeTrue();
});

it('does not create duplicates when run again', function () {
    $this->seed(ProductionSeeder::class);
    $this->seed(ProductionSeeder::class);

    expect(Role::count())->toBe(2)
        ->and(Category::count())->toBe(10)
        ->and(User::count())->toBe(1);
});

it('does not overwrite an existing admin password', function () {
    $this->seed(ProductionSeeder::class);

    config(['admin.password' => 'a-different-password']);

    $this->seed(ProductionSeeder::class);

    $admin = User::where('email', 'admin@example.com')->firstOrFail();

    expect(Hash::check('super-secret-password', $admin->password))->toBeTrue();
});

it('skips the admin user when credentials are missing', function () {
    config(['admin.email' => null, 'admin.password' => null]);

    $this->seed(ProductionSeeder::class);

    expect(User::count())->toBe(0)
        ->and(Category::count())->toBe(10);
});
