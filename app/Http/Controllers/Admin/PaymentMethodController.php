<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Payment\Actions\CreatePaymentMethod;
use App\Domain\Payment\Enums\PaymentMethodType;
use App\Domain\Payment\Models\BankAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentMethodRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PaymentMethodController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/payment-methods/index', [
            'methods' => BankAccount::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (BankAccount $method): array => [
                    'id' => $method->id,
                    'is_active' => $method->is_active,
                    ...$method->snapshot(),
                ])
                ->values(),
        ]);
    }

    public function store(StorePaymentMethodRequest $request, CreatePaymentMethod $createPaymentMethod): RedirectResponse
    {
        $createPaymentMethod->handle(
            [
                'type' => PaymentMethodType::from($request->string('type')->toString()),
                'name' => $request->string('name')->toString(),
                'account_number' => $request->filled('account_number') ? $request->string('account_number')->toString() : null,
                'account_holder' => $request->filled('account_holder') ? $request->string('account_holder')->toString() : null,
                'is_active' => $request->boolean('is_active', true),
            ],
            $request->file('qris'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Metode pembayaran ditambahkan.']);

        return back();
    }

    public function toggle(BankAccount $paymentMethod): RedirectResponse
    {
        $paymentMethod->update(['is_active' => ! $paymentMethod->is_active]);

        return back();
    }

    public function destroy(BankAccount $paymentMethod): RedirectResponse
    {
        $paymentMethod->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Metode pembayaran dihapus.']);

        return back();
    }
}
