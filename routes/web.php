<?php

use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PaymentProofController as AdminPaymentProofController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
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
    Route::inertia('dashboard', 'dashboard')->middleware('admin.home')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('kelola')->name('admin.')->group(function () {
    Route::get('pembayaran', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('pembayaran/{payment}/verifikasi', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('pembayaran/{payment}/tolak', [PaymentController::class, 'reject'])->name('payments.reject');
    Route::get('metode-pembayaran', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::post('metode-pembayaran', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::patch('metode-pembayaran/{paymentMethod}', [PaymentMethodController::class, 'toggle'])->name('payment-methods.toggle');
    Route::delete('metode-pembayaran/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
    Route::get('reservasi', [AdminReservationController::class, 'index'])->name('reservations.index');
    Route::get('reservasi/{reservation}', [AdminReservationController::class, 'show'])->name('reservations.show');
    Route::post('reservasi/{reservation}/batal', [AdminReservationController::class, 'cancel'])->name('reservations.cancel');

    Route::get('acara', [AdminEventController::class, 'index'])->name('events.index');
    Route::get('acara/baru', [AdminEventController::class, 'create'])->name('events.create');
    Route::post('acara', [AdminEventController::class, 'store'])->name('events.store');
    Route::get('acara/{event}', [AdminEventController::class, 'edit'])->name('events.edit');
    Route::put('acara/{event}', [AdminEventController::class, 'update'])->name('events.update');
    Route::post('acara/{event}/pendaftaran/{registration}/batal', [AdminEventController::class, 'cancelRegistration'])->name('events.registrations.cancel');

    Route::get('bukti/{proof}', [AdminPaymentProofController::class, 'show'])
        ->middleware('signed')
        ->name('proofs.show');
});

require __DIR__.'/settings.php';
