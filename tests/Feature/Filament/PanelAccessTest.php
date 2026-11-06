<?php

use App\Models\User;

$adminPages = [
    'dashboard' => '/admin',
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

it('redirects guests to the admin login page', function (string $url) {
    $this->get($url)->assertRedirect('/admin/login');
})->with($adminPages);

it('forbids registered visitors who are not admins', function (string $url) {
    $this->actingAs(User::factory()->create());

    $this->get($url)->assertForbidden();
})->with($adminPages);

it('lets admins open every admin page', function (string $url) {
    $this->actingAs(User::factory()->admin()->create());

    $this->get($url)->assertOk();
})->with($adminPages);
