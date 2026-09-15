<?php

use App\Models\Goal;
use App\Models\User;

it('lists goals by priority, then by nearest target date with undated goals last', function () {
    $user = User::factory()->member()->create();

    Goal::factory()->for($user)->create(['name' => 'Low', 'priority' => 'low', 'target_date' => '2027-01-01']);
    Goal::factory()->for($user)->create(['name' => 'High undated', 'priority' => 'high', 'target_date' => null]);
    Goal::factory()->for($user)->create(['name' => 'High late', 'priority' => 'high', 'target_date' => '2027-06-01']);
    Goal::factory()->for($user)->create(['name' => 'High soon', 'priority' => 'high', 'target_date' => '2027-02-01']);
    Goal::factory()->for($user)->create(['name' => 'Medium', 'priority' => 'medium', 'target_date' => null]);
    Goal::factory()->create(['name' => 'Someone else', 'priority' => 'high']);

    $this->actingAs($user)
        ->get(route('goals.index'))
        ->assertInertia(fn ($page) => $page
            ->component('Goals/Index')
            ->has('goals', 5)
            ->where('goals.0.name', 'High soon')
            ->where('goals.1.name', 'High late')
            ->where('goals.2.name', 'High undated')
            ->where('goals.3.name', 'Medium')
            ->where('goals.4.name', 'Low')
        );
});
