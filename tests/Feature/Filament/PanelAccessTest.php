<?php

use App\Models\User;

$adminPages = [
    'dashboard admin' => '/admin',
    'profil desa' => '/admin/pages',
    'destinasi' => '/admin/destinations',
    'produk lokal' => '/admin/local-products',
    'fasilitas' => '/admin/facilities',
    'aturan harga' => '/admin/pricing-rules',
    'tanggal diblokir' => '/admin/blocked-dates',
    'event' => '/admin/events',
    'reservasi' => '/admin/reservations',
    'pembayaran' => '/admin/payments',
    'metode pembayaran' => '/admin/bank-accounts',
];

it('sends guests to the site login page instead of a separate admin login', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with($adminPages);

it('has no admin login page of its own', function () {
    $this->get('/admin/login')->assertNotFound();
});

it('forbids registered visitors who are not admins', function (string $url) {
    $this->actingAs(User::factory()->create());

    $this->get($url)->assertForbidden();
})->with($adminPages);

it('lets admins open every admin page', function (string $url) {
    $this->actingAs(User::factory()->admin()->create());

    $this->get($url)->assertOk();
})->with($adminPages);

it('signs admins out from the panel and returns them to the public site', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/admin/logout')->assertRedirect(route('home'));

    $this->assertGuest();
});
