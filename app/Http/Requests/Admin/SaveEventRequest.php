<?php

namespace App\Http\Requests\Admin;

use App\Domain\Event\Enums\EventStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEventRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $dateTime = ['date_format:Y-m-d\TH:i'];

        return [
            'title_id' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'description_id' => ['nullable', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'requirements_id' => ['nullable', 'string', 'max:2000'],
            'requirements_en' => ['nullable', 'string', 'max:2000'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'starts_at' => ['required', ...$dateTime],
            'ends_at' => ['nullable', ...$dateTime, 'after:starts_at'],
            'fee' => ['required', 'integer', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'registration_opens_at' => ['nullable', ...$dateTime],
            'registration_closes_at' => ['nullable', ...$dateTime, 'after:registration_opens_at'],
            'status' => ['required', Rule::enum(EventStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title_id.required' => 'Judul (Indonesia) wajib diisi.',
            'starts_at.required' => 'Waktu mulai wajib diisi.',
            'ends_at.after' => 'Waktu selesai harus setelah waktu mulai.',
            'registration_closes_at.after' => 'Penutupan pendaftaran harus setelah pembukaan.',
            'fee.required' => 'Biaya wajib diisi (isi 0 untuk acara gratis).',
            'quota.min' => 'Kuota minimal 1.',
        ];
    }
}
