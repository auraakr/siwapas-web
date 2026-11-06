<?php

namespace App\Http\Controllers\Public;

use App\Domain\Payment\Actions\SubmitPaymentProof;
use App\Domain\Payment\Exceptions\PaymentNotPossible;
use App\Domain\Payment\Models\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StorePaymentProofRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;

class PaymentProofController extends Controller
{
    public function store(StorePaymentProofRequest $request, Order $order, SubmitPaymentProof $submitPaymentProof): RedirectResponse
    {
        try {
            $submitPaymentProof->handle(
                $order,
                $request->file('proof'),
                $request->filled('sender_name') ? $request->string('sender_name')->toString() : null,
                $request->filled('transfer_date') ? Date::parse($request->string('transfer_date')->toString()) : null,
                $request->filled('claimed_amount') ? $request->integer('claimed_amount') : null,
            );
        } catch (PaymentNotPossible) {
            return back()->withErrors(['proof' => 'Pesanan ini tidak dapat menerima bukti transfer lagi.']);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Bukti transfer diterima. Kami akan memverifikasinya.']);

        return to_route('orders.show', $order);
    }
}
