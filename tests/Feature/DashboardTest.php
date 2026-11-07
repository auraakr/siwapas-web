<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('registered visitors without the admin role can visit the dashboard', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('admins are sent to the admin panel instead of the visitor dashboard', function () {
    $this->actingAs(User::factory()->admin()->create());

    $response = $this->get(route('dashboard'));
    $response->assertRedirect('/admin');
});

test('admins who sign in land on the admin panel dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->followingRedirects()
        ->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertOk()
        ->assertSee('Menunggu verifikasi');

    $this->assertAuthenticatedAs($admin);
});

test('registered visitors who sign in land on the visitor dashboard', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));
});

test('admins signing in through the login form get a full page visit to the panel', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request())])
        ->get(route('dashboard'))
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', '/admin');
});
