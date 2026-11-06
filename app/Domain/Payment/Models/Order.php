<?php

namespace App\Domain\Payment\Models;

use App\Domain\Booking\Models\Reservation;
use App\Domain\Event\Models\EventRegistration;
use App\Domain\Payment\Enums\OrderKind;
use App\Domain\Payment\Enums\OrderStatus;
use Carbon\CarbonImmutable;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $code
 * @property string $access_token
 * @property OrderKind $kind
 * @property string $customer_name
 * @property string $customer_phone
 * @property string|null $customer_email
 * @property string|null $notes
 * @property OrderStatus $status
 * @property int $subtotal
 * @property int $unique_code
 * @property int $total
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $confirmed_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['code', 'access_token', 'kind', 'customer_name', 'customer_phone', 'customer_email', 'notes', 'status', 'subtotal', 'unique_code', 'total', 'expires_at', 'confirmed_at'])]
#[Hidden(['access_token'])]
#[UseFactory(OrderFactory::class)]

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => OrderKind::class,
            'status' => OrderStatus::class,
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasOne<Reservation, $this>
     */
    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class);
    }

    /**
     * @return HasOne<EventRegistration, $this>
     */
    public function eventRegistration(): HasOne
    {
        return $this->hasOne(EventRegistration::class);
    }
}
