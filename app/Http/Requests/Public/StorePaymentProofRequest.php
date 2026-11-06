<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentProofRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'proof' => [
                'required',
                'file',
                'mimes:'.implode(',', (array) config('siwapas.payment.proof_mimes')),
                'max:'.config('siwapas.payment.proof_max_kb'),
            ],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'transfer_date' => ['nullable', 'date_format:Y-m-d'],
            'claimed_amount' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proof.required' => 'Bukti transfer wajib diunggah.',
            'proof.mimes' => 'Bukti transfer harus berupa JPG, PNG, atau PDF.',
            'proof.max' => 'Ukuran bukti transfer maksimal 5 MB.',
        ];
    }
}
