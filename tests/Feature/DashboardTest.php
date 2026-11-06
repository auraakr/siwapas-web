<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('admins can visit the dashboard', function () {
    $this->actingAs(User::factory()->admin()->create());

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('registered visitors without the admin role are sent back to the home page', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('home'));
});
