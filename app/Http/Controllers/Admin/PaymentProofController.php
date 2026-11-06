<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Payment\Models\PaymentProof;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofController extends Controller
{
    /**
     * Proofs live on a private disk and are only reachable through a short-lived signed URL.
     */
    public function show(PaymentProof $proof): StreamedResponse
    {
        return Storage::disk($proof->disk)->response($proof->path, $proof->original_name);
    }
}
