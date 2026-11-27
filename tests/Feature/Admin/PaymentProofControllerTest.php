<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * The Filament payment screen links to proof files through this short-lived signed route.
 */
it('serves the file through a valid signed link to an admin', function () {
    Storage::fake('local');
    [, , $proof] = orderAwaitingVerification();
    Storage::disk('local')->put($proof->path, 'proof-bytes');
    $url = URL::temporarySignedRoute('admin.proofs.show', now()->addMinutes(5), ['proof' => $proof->id]);

    $this->actingAs(User::factory()->admin()->create())->get($url)->assertOk();
});

it('refuses a link without a valid signature', function () {
    Storage::fake('local');
    [, , $proof] = orderAwaitingVerification();
    Storage::disk('local')->put($proof->path, 'proof-bytes');

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.proofs.show', $proof))
        ->assertForbidden();
});

it('refuses an expired link', function () {
    Storage::fake('local');
    [, , $proof] = orderAwaitingVerification();
    $url = URL::temporarySignedRoute('admin.proofs.show', now()->addMinutes(5), ['proof' => $proof->id]);

    $this->travel(6)->minutes();

    $this->actingAs(User::factory()->admin()->create())->get($url)->assertForbidden();
});

it('refuses a valid link to a non-admin', function () {
    Storage::fake('local');
    [, , $proof] = orderAwaitingVerification();
    $url = URL::temporarySignedRoute('admin.proofs.show', now()->addMinutes(5), ['proof' => $proof->id]);

    $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
});

it('sends guests to the login page', function () {
    [, , $proof] = orderAwaitingVerification();
    $url = URL::temporarySignedRoute('admin.proofs.show', now()->addMinutes(5), ['proof' => $proof->id]);

    $this->get($url)->assertRedirect(route('login'));
});
