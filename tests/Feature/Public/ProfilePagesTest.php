<?php

use App\Domain\Content\Models\Page;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the village profile sections in the order they should read', function () {
    Page::factory()->create(['key' => 'vision', 'slug' => 'visi-misi', 'status' => 'published']);
    Page::factory()->create(['key' => 'profile', 'slug' => 'profil-desa', 'status' => 'published']);
    Page::factory()->create(['key' => 'history', 'slug' => 'sejarah', 'status' => 'published']);

    $this->get('/profil-desa')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/village')
            ->has('sections', 3)
            ->where('sections.0.key', 'profile')
            ->where('sections.1.key', 'history')
            ->where('sections.2.key', 'vision'));
});

it('leaves draft and unrelated pages off the village profile', function () {
    Page::factory()->create(['key' => 'history', 'slug' => 'sejarah', 'status' => 'draft']);
    Page::factory()->create(['key' => 'terms', 'slug' => 'ketentuan-reservasi', 'status' => 'published']);

    $this->get('/profil-desa')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('sections', 0));
});

it('shows the visit page with the published info and terms', function () {
    Page::factory()->create(['key' => 'visit_info', 'slug' => 'informasi-reservasi', 'status' => 'published']);
    Page::factory()->create(['key' => 'terms', 'slug' => 'ketentuan-reservasi', 'status' => 'draft']);

    $this->get('/kunjungan')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/visit')
            ->where('info.title', fn ($title) => filled($title))
            ->where('terms', null));
});

it('shows the visit page even before any text has been written', function () {
    $this->get('/kunjungan')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/visit')
            ->where('info', null)
            ->where('terms', null));
});
