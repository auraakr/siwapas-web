<?php

use App\Domain\Catalog\Models\Facility;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the site in Indonesian by default, whatever the app locale is', function () {
    config()->set('app.locale', 'en');

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'id'));

    expect(app()->getLocale())->toBe('id');
});

it('remembers the language a visitor picks', function (string $locale) {
    $this->from(route('facilities.index'))
        ->post(route('locale.update'), ['locale' => $locale])
        ->assertRedirect(route('facilities.index'))
        ->assertSessionHas('locale', $locale);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', $locale));
})->with(['id', 'en']);

it('rejects a language the site does not offer', function (mixed $locale) {
    $this->post(route('locale.update'), ['locale' => $locale])
        ->assertSessionHasErrors('locale');

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'id'));
})->with([
    'unsupported language' => 'fr',
    'empty value' => '',
    'array value' => [['en']],
]);

it('shows the English text of bilingual content after switching, and falls back to Indonesian when it is missing', function () {
    Facility::factory()->create(['slug' => 'dua-bahasa', 'name' => ['id' => 'Aula Desa', 'en' => 'Village Hall']]);
    Facility::factory()->create(['slug' => 'satu-bahasa', 'name' => ['id' => 'Saung Tepi Sungai'], 'sort_order' => 1]);

    $this->get(route('facilities.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('facilities.0.name', 'Aula Desa')
            ->where('facilities.1.name', 'Saung Tepi Sungai'));

    $this->post(route('locale.update'), ['locale' => 'en']);

    $this->get(route('facilities.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('facilities.0.name', 'Village Hall')
            ->where('facilities.1.name', 'Saung Tepi Sungai'));
});

it('does not change the language of the admin panel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('locale.update'), ['locale' => 'en']);

    $this->get('/admin')->assertOk()->assertSee('Menunggu verifikasi');
});
