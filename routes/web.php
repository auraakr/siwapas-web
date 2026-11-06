<?php

use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\EventRegistrationController;
use App\Http\Controllers\Public\FacilityController;
use App\Http\Controllers\Public\OrderController;
use App\Http\Controllers\Public\PaymentProofController;
use App\Http\Controllers\Public\ReservationController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('fasilitas', [FacilityController::class, 'index'])->name('facilities.index');
Route::get('fasilitas/{facility:slug}', [FacilityController::class, 'show'])->name('facilities.show');
Route::post('fasilitas/{facility:slug}/reservasi', [ReservationController::class, 'store'])
    ->middleware('throttle:public-write')
    ->name('reservations.store');

Route::get('acara', [EventController::class, 'index'])->name('events.index');
Route::get('acara/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::post('acara/{event:slug}/daftar', [EventRegistrationController::class, 'store'])
    ->middleware('throttle:public-write')
    ->name('events.register');

Route::get('pesanan/{order:access_token}', [OrderController::class, 'show'])->name('orders.show');
Route::post('pesanan/{order:access_token}/bukti', [PaymentProofController::class, 'store'])
    ->middleware('throttle:public-write')
    ->name('orders.proof.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
