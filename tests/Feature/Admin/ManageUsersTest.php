<?php

use App\Models\Category;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('lists users with their totals', function () {
    $member = User::factory()->member()->create();
    $member->incomes()->create(['amount' => 300, 'category_id' => Category::factory()->income()->create()->id]);
    $member->expenses()->create(['amount' => 120, 'category_id' => Category::factory()->expense()->create()->id]);

    $this->actingAs($this->admin)
        ->get('/admin/users')
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Users')
            ->has('users.data', 2)
            ->has('roles', 2)
        );
});

it('filters users by name or email', function () {
    User::factory()->member()->create(['name' => 'Zainab', 'email' => 'zainab@example.com']);

    $this->actingAs($this->admin)
        ->get('/admin/users?search=zainab')
        ->assertInertia(fn ($page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.name', 'Zainab')
        );
});

it('promotes a user to admin', function () {
    $member = User::factory()->member()->create();
    $adminRoleId = Role::where('name', Role::ADMIN)->value('id');

    $this->actingAs($this->admin)
        ->patch("/admin/users/{$member->id}/role", ['role_id' => $adminRoleId])
        ->assertSessionHasNoErrors();

    expect($member->fresh()->isAdmin())->toBeTrue();
});

it('prevents an admin from changing their own role', function () {
    $memberRoleId = Role::where('name', Role::USER)->value('id');

    $this->actingAs($this->admin)
        ->patch("/admin/users/{$this->admin->id}/role", ['role_id' => $memberRoleId])
        ->assertSessionHasErrors('role_id');

    expect($this->admin->fresh()->isAdmin())->toBeTrue();
});

it('rejects an unknown role', function () {
    $member = User::factory()->member()->create();

    $this->actingAs($this->admin)
        ->patch("/admin/users/{$member->id}/role", ['role_id' => 999])
        ->assertSessionHasErrors('role_id');
});

it('blocks non-admins from changing roles', function () {
    $member = User::factory()->member()->create();

    $this->actingAs(User::factory()->member()->create())
        ->patch("/admin/users/{$member->id}/role", ['role_id' => 1])
        ->assertForbidden();
});
