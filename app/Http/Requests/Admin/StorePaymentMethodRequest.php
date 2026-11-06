<?php

namespace App\Http\Requests\Admin;

use App\Domain\Payment\Enums\PaymentMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentMethodRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(PaymentMethodType::class)],
            'name' => ['required', 'string', 'max:100'],
            'account_number' => ['required_if:type,bank', 'nullable', 'string', 'max:50'],
            'account_holder' => ['required_if:type,bank', 'nullable', 'string', 'max:255'],
            'qris' => ['required_if:type,qris', 'nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:'.config('siwapas.payment.proof_max_kb')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama metode pembayaran wajib diisi.',
            'account_number.required_if' => 'Nomor rekening wajib diisi.',
            'account_holder.required_if' => 'Nama pemilik rekening wajib diisi.',
            'qris.required_if' => 'Gambar QRIS wajib diunggah.',
            'qris.mimes' => 'Gambar QRIS harus berupa JPG, PNG, atau WEBP.',
        ];
    }
}
