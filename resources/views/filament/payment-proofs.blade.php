@php
    use Illuminate\Support\Facades\URL;
@endphp

<div class="space-y-3 text-sm">
    @forelse ($payment->proofs as $proof)
        <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
            <a
                href="{{ URL::temporarySignedRoute('admin.proofs.show', now()->addMinutes(5), ['proof' => $proof->id]) }}"
                target="_blank"
                rel="noreferrer"
                class="font-medium text-primary-600 underline"
            >
                {{ $proof->original_name }}
            </a>
            <p>
                @if ($proof->sender_name) Pengirim: {{ $proof->sender_name }} · @endif
                @if ($proof->claimed_amount !== null) Nominal diklaim: Rp {{ number_format($proof->claimed_amount, 0, ',', '.') }} · @endif
                Nominal seharusnya: Rp {{ number_format($payment->expected_amount, 0, ',', '.') }}
            </p>
        </div>
    @empty
        <p class="text-gray-500">Belum ada bukti transfer.</p>
    @endforelse
</div>
