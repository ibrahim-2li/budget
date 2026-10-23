<?php

use App\Models\BiometricToken;
use App\Models\User;

test('a password sign-in offers fingerprint sign-in', function () {
    $user = User::factory()->create();

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('budget'))
        ->assertInertiaFlash('offerBiometric', true);
});

test('a signed-in user can get a device token', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('biometric.store'))->assertOk();

    $token = $response->json('token');

    expect($token)->toHaveLength(64)
        ->and(BiometricToken::findByToken($token)?->user_id)->toBe($user->id)
        ->and(BiometricToken::first()->token_hash)->not->toBe($token);
});

test('guests cannot get a device token', function () {
    $this->postJson(route('biometric.store'))->assertUnauthorized();

    expect(BiometricToken::count())->toBe(0);
});

test('a valid device token signs the user in', function () {
    $user = User::factory()->create();
    $token = BiometricToken::issueFor($user);

    $this->post(route('login.biometric'), ['token' => $token])
        ->assertRedirect(route('budget'));

    $this->assertAuthenticatedAs($user);
    expect(BiometricToken::first()->last_used_at)->not->toBeNull();
});

test('an unknown device token is rejected', function () {
    BiometricToken::issueFor(User::factory()->create());

    $this->from(route('login'))
        ->post(route('login.biometric'), ['token' => str_repeat('a', 64)])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('biometric');

    $this->assertGuest();
});

test('a device token stops working once its user is deleted', function () {
    $user = User::factory()->create();
    $token = BiometricToken::issueFor($user);

    $user->delete();

    $this->post(route('login.biometric'), ['token' => $token])
        ->assertSessionHasErrors('biometric');

    $this->assertGuest();
});

test('biometric sign-in is rate limited', function () {
    foreach (range(1, 10) as $attempt) {
        $this->post(route('login.biometric'), ['token' => str_repeat('a', 64)]);
    }

    $this->post(route('login.biometric'), ['token' => str_repeat('a', 64)])
        ->assertTooManyRequests();
});

test('signing out tells the login page not to ask for a fingerprint', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('home'))
        ->assertInertiaFlash('loggedOut', true);
});
