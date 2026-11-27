<?php

use App\Http\Controllers\Admin\PaymentProofController as AdminPaymentProofController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\DestinationController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\EventRegistrationController;
use App\Http\Controllers\Public\FacilityController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LocaleController;
use App\Http\Controllers\Public\OrderController;
use App\Http\Controllers\Public\PaymentProofController;
use App\Http\Controllers\Public\ReservationController;
use App\Http\Controllers\Public\VillageController;
use App\Http\Controllers\Public\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::post('bahasa', [LocaleController::class, 'update'])->name('locale.update');

Route::get('fasilitas', [FacilityController::class, 'index'])->name('facilities.index');
Route::get('fasilitas/{facility:slug}', [FacilityController::class, 'show'])->name('facilities.show');
Route::post('fasilitas/{facility:slug}/reservasi', [ReservationController::class, 'store'])
    ->middleware('throttle:public-write')
    ->name('reservations.store');

Route::get('kunjungan', VisitController::class)->name('visit.show');
Route::get('acara', [EventController::class, 'index'])->name('events.index');
Route::get('acara/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::post('acara/{event:slug}/daftar', [EventRegistrationController::class, 'store'])
    ->middleware('throttle:public-write')
    ->name('events.register');
Route::get('destinasi', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('destinasi/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');
Route::get('galeri', GalleryController::class)->name('gallery.index');
Route::get('profil-desa', VillageController::class)->name('village.show');
Route::get('kontak', [ContactController::class, 'show'])->name('contact.show');
Route::post('kontak', [ContactController::class, 'store'])
    ->middleware('throttle:public-write')
    ->name('contact.store');

Route::get('pesanan/{order:access_token}', [OrderController::class, 'show'])->name('orders.show');
Route::post('pesanan/{order:access_token}/bukti', [PaymentProofController::class, 'store'])
    ->middleware('throttle:public-write')
    ->name('orders.proof.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->middleware('admin.panel')->name('dashboard');
});

// The admin panel itself is Filament (/admin). This route only serves payment proof files to it
// through short-lived signed links.
Route::middleware(['auth', 'verified', 'admin'])->prefix('kelola')->name('admin.')->group(function () {
    Route::get('bukti/{proof}', [AdminPaymentProofController::class, 'show'])
        ->middleware('signed')
        ->name('proofs.show');
});

require __DIR__.'/settings.php';
