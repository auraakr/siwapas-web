<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Enums\PaymentMethodType;
use App\Domain\Payment\Models\BankAccount;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreatePaymentMethod
{
    /**
     * @param  array{type: PaymentMethodType, name: string, account_number?: string|null, account_holder?: string|null, is_active?: bool}  $data
     */
    public function handle(array $data, ?UploadedFile $qris = null): BankAccount
    {
        return DB::transaction(function () use ($data, $qris): BankAccount {
            $isQris = $data['type'] === PaymentMethodType::Qris;

            $method = BankAccount::create([
                'type' => $data['type'],
                'bank_name' => $data['name'],
                'account_number' => $isQris ? null : ($data['account_number'] ?? null),
                'account_holder' => $isQris ? null : ($data['account_holder'] ?? null),
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => (int) BankAccount::query()->max('sort_order') + 1,
            ]);

            if ($isQris && $qris !== null) {
                $method->addMedia($qris)->toMediaCollection(BankAccount::QRIS_COLLECTION);
            }

            return $method;
        });
    }
}
