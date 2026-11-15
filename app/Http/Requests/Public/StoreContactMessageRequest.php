<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    /**
     * People type numbers as "0812-3456-7890"; keep only what the phone rule needs.
     */
    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');

        if (is_string($phone)) {
            $clean = preg_replace('/[\s().-]+/', '', $phone);

            $this->merge(['phone' => $clean === '' ? null : $clean]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'required_without:email', 'string', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'phone.required_without' => 'Isi nomor WhatsApp atau email agar kami bisa membalas.',
            'phone.regex' => 'Nomor telepon tidak valid. Gunakan format 08xxxxxxxxxx.',
            'email.required_without' => 'Isi nomor WhatsApp atau email agar kami bisa membalas.',
            'email.email' => 'Format email tidak valid.',
            'message.required' => 'Pesan wajib diisi.',
            'message.min' => 'Pesan terlalu singkat. Tulis minimal 10 karakter.',
            'message.max' => 'Pesan terlalu panjang. Maksimal 2000 karakter.',
        ];
    }
}
