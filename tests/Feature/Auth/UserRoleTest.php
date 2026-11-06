<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'admin'])->get('/_admin-only', fn () => 'ok');
});

test('registered users get the guest role', function () {
    $this->post(route('register.store'), [
        'name' => 'Tamu Baru',
        'email' => 'tamu@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(User::where('email', 'tamu@example.com')->firstOrFail()->role)->toBe(UserRole::Guest);
});

test('registration cannot set the role', function () {
    $this->post(route('register.store'), [
        'name' => 'Nakal',
        'email' => 'nakal@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'admin',
    ]);

    expect(User::where('email', 'nakal@example.com')->firstOrFail()->role)->toBe(UserRole::Guest);
});

test('admins can reach admin-only routes', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/_admin-only')
        ->assertOk();
});

test('guests cannot reach admin-only routes', function () {
    $this->actingAs(User::factory()->create())
        ->get('/_admin-only')
        ->assertForbidden();
});
