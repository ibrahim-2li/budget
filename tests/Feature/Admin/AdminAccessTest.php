<?php

use App\Models\Category;
use App\Models\User;

it('redirects guests to the login page', function (string $url) {
    $this->get($url)->assertRedirect('/login');
})->with(['/admin', '/admin/users', '/admin/categories']);

it('forbids non-admin users', function (string $url) {
    $this->actingAs(User::factory()->member()->create())
        ->get($url)
        ->assertForbidden();
})->with(['/admin', '/admin/users', '/admin/categories']);

it('forbids users without any role', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

it('allows admins', function (string $url) {
    $this->actingAs(User::factory()->admin()->create())
        ->get($url)
        ->assertOk();
})->with(['/admin', '/admin/users', '/admin/categories']);

it('shows aggregate stats on the dashboard', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();

    $category = Category::factory()->income()->create();
    $member->incomes()->create(['amount' => 500, 'category_id' => $category->id]);
    $member->expenses()->create(['amount' => 200, 'category_id' => Category::factory()->expense()->create()->id]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('stats.users', 2)
            ->where('stats.incomeEntries', 1)
            ->where('stats.totalIncome', 500)
            ->where('stats.totalExpenses', 200)
            ->has('monthlyActivity', 6)
        );
});
