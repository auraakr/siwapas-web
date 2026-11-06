<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRegistrationRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'participants' => ['required', 'array', 'min:1', 'max:10'],
            'participants.*.name' => ['required', 'string', 'max:255'],
            'participants.*.phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Nomor telepon tidak valid. Gunakan format 08xxxxxxxxxx.',
            'participants.*.name.required' => 'Nama peserta wajib diisi.',
        ];
    }
}
