<?php

namespace App\Domain\Payment\Models;

use App\Domain\Payment\Enums\PaymentMethodType;
use Carbon\CarbonImmutable;
use Database\Factories\BankAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * A place visitors can send a manual payment to: a bank account or a QRIS code.
 *
 * @property int $id
 * @property PaymentMethodType $type
 * @property string $bank_name Label shown to visitors, e.g. "BCA" or "QRIS".
 * @property string|null $account_number
 * @property string|null $account_holder
 * @property bool $is_active
 * @property int $sort_order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['type', 'bank_name', 'account_number', 'account_holder', 'is_active', 'sort_order'])]
#[UseFactory(BankAccountFactory::class)]
class BankAccount extends Model implements HasMedia
{
    /** @use HasFactory<BankAccountFactory> */
    use HasFactory, InteractsWithMedia;

    public const QRIS_COLLECTION = 'qris';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PaymentMethodType::class,
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::QRIS_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * What a visitor needs to see to pay with this method.
     *
     * @return array{type: string, name: string, account_number: string|null, account_holder: string|null, qris_url: string|null}
     */
    public function snapshot(): array
    {
        return [
            'type' => $this->type->value,
            'name' => $this->bank_name,
            'account_number' => $this->account_number,
            'account_holder' => $this->account_holder,
            'qris_url' => $this->type === PaymentMethodType::Qris
                ? ($this->getFirstMediaUrl(self::QRIS_COLLECTION) ?: null)
                : null,
        ];
    }
}
