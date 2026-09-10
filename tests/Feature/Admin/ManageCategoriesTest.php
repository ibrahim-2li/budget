<?php

use App\Models\Category;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('lists categories with their usage counts', function () {
    $category = Category::factory()->expense()->create();
    $user = User::factory()->member()->create();
    $user->expenses()->create(['amount' => 25, 'category_id' => $category->id]);

    $this->actingAs($this->admin)
        ->get('/admin/categories')
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Categories')
            ->has('categories', 1)
            ->where('categories.0.usage_count', 1)
        );
});

it('creates a category', function () {
    $this->actingAs($this->admin)
        ->post('/admin/categories', [
            'name' => 'Groceries',
            'type' => 'expense',
            'color' => 'text-amber-500',
            'icon' => 'fa-solid fa-cart-shopping',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('categories', ['name' => 'Groceries', 'type' => 'expense']);
});

it('rejects a duplicate name within the same type', function () {
    Category::factory()->create(['name' => 'Rent', 'type' => 'expense']);

    $this->actingAs($this->admin)
        ->post('/admin/categories', [
            'name' => 'Rent',
            'type' => 'expense',
            'color' => 'text-red-500',
            'icon' => 'fa-solid fa-house',
        ])
        ->assertSessionHasErrors('name');
});

it('allows the same name across different types', function () {
    Category::factory()->create(['name' => 'Bonus', 'type' => 'expense']);

    $this->actingAs($this->admin)
        ->post('/admin/categories', [
            'name' => 'Bonus',
            'type' => 'income',
            'color' => 'text-green-500',
            'icon' => 'fa-solid fa-gift',
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('categories', 2);
});

it('requires every field', function () {
    $this->actingAs($this->admin)
        ->post('/admin/categories', [])
        ->assertSessionHasErrors(['name', 'type', 'color', 'icon']);
});

it('updates a category', function () {
    $category = Category::factory()->expense()->create(['name' => 'Food']);

    $this->actingAs($this->admin)
        ->patch("/admin/categories/{$category->id}", [
            'name' => 'Dining out',
            'type' => 'expense',
            'color' => 'text-orange-500',
            'icon' => 'fa-solid fa-burger',
        ])
        ->assertSessionHasNoErrors();

    expect($category->fresh()->name)->toBe('Dining out');
});

it('keeps its own name valid when updating', function () {
    $category = Category::factory()->expense()->create(['name' => 'Food']);

    $this->actingAs($this->admin)
        ->patch("/admin/categories/{$category->id}", [
            'name' => 'Food',
            'type' => 'expense',
            'color' => 'text-teal-500',
            'icon' => 'fa-solid fa-burger',
        ])
        ->assertSessionHasNoErrors();
});

it('deletes an unused category', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/admin/categories/{$category->id}")
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

it('refuses to delete a category that is still in use', function () {
    $category = Category::factory()->income()->create();
    User::factory()->member()->create()->incomes()->create(['amount' => 100, 'category_id' => $category->id]);

    $this->actingAs($this->admin)
        ->delete("/admin/categories/{$category->id}")
        ->assertSessionHasErrors('category');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

it('blocks non-admins from writing categories', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->member()->create())
        ->post('/admin/categories', ['name' => 'X', 'type' => 'expense', 'color' => 'c', 'icon' => 'i'])
        ->assertForbidden();

    $this->actingAs(User::factory()->member()->create())
        ->delete("/admin/categories/{$category->id}")
        ->assertForbidden();
});
