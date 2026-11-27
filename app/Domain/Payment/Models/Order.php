<?php

namespace App\Domain\Payment\Models;

use App\Domain\Booking\Models\Reservation;
use App\Domain\Event\Models\EventRegistration;
use App\Domain\Payment\Enums\CancellationParty;
use App\Domain\Payment\Enums\OrderKind;
use App\Domain\Payment\Enums\OrderStatus;
use App\Domain\Payment\Enums\PaymentType;
use App\Domain\Payment\Enums\SettlementMethod;
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
 * @property int|null $dp_percentage
 * @property int|null $dp_amount
 * @property int|null $remaining_amount
 * @property SettlementMethod|null $settlement_method
 * @property CarbonImmutable|null $settled_at
 * @property CancellationParty|null $cancelled_by
 * @property string|null $resolution_note
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $confirmed_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['code', 'access_token', 'kind', 'customer_name', 'customer_phone', 'customer_email', 'notes', 'status', 'subtotal', 'unique_code', 'total', 'dp_percentage', 'dp_amount', 'remaining_amount', 'settlement_method', 'settled_at', 'cancelled_by', 'resolution_note', 'expires_at', 'confirmed_at'])]
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
            'settlement_method' => SettlementMethod::class,
            'settled_at' => 'datetime',
            'cancelled_by' => CancellationParty::class,
        ];
    }

    /**
     * Reservations pay a down payment first; event tickets and older orders are paid in full.
     */
    public function requiresDownPayment(): bool
    {
        return $this->dp_amount !== null;
    }

    /**
     * The amount of the first transfer: the down payment (or the full price) plus the unique code.
     */
    public function firstPaymentAmount(): int
    {
        return ($this->dp_amount ?? $this->subtotal) + $this->unique_code;
    }

    public function paymentDeadlinePassed(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * The visitor can upload a proof for the down payment (or full payment) until the deadline.
     */
    public function acceptsFirstPaymentProof(): bool
    {
        return $this->status === OrderStatus::PendingPayment && ! $this->paymentDeadlinePassed();
    }

    /**
     * After the down payment is verified, the settlement can be paid by transfer until the end of
     * the check-out day (Asia/Jakarta). After that the visitor settles with the managers directly.
     */
    public function acceptsSettlementProof(): bool
    {
        if ($this->status !== OrderStatus::DpPaid || ! $this->requiresDownPayment() || $this->reservation === null) {
            return false;
        }

        $today = now(config('siwapas.display_timezone'))->toDateString();

        return $today <= $this->reservation->check_out->toDateString();
    }

    /**
     * The most recent payment of the given type, if any.
     */
    public function latestPaymentOf(PaymentType $type): ?Payment
    {
        return $this->payments()->where('type', $type)->latest('id')->first();
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
