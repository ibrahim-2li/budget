<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Salary', 'type' => 'income', 'color' => 'text-green-500', 'icon' => 'fa-solid fa-money-bill-wave'],
            ['name' => 'Freelance', 'type' => 'income', 'color' => 'text-blue-500', 'icon' => 'fa-solid fa-laptop-code'],
            ['name' => 'Investments', 'type' => 'income', 'color' => 'text-purple-500', 'icon' => 'fa-solid fa-chart-line'],
            ['name' => 'Rent', 'type' => 'expense', 'color' => 'text-red-500', 'icon' => 'fa-solid fa-house'],
            ['name' => 'Food', 'type' => 'expense', 'color' => 'text-orange-500', 'icon' => 'fa-solid fa-burger'],
            ['name' => 'Transport', 'type' => 'expense', 'color' => 'text-yellow-500', 'icon' => 'fa-solid fa-bus'],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => 'text-pink-500', 'icon' => 'fa-solid fa-ticket'],
            ['name' => 'Shopping', 'type' => 'expense', 'color' => 'text-teal-500', 'icon' => 'fa-solid fa-cart-shopping'],
            ['name' => 'Health', 'type' => 'expense', 'color' => 'text-rose-500', 'icon' => 'fa-solid fa-staff-snake'],
            ['name' => 'Other', 'type' => 'expense', 'color' => 'text-gray-500', 'icon' => 'fa-solid fa-box'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
