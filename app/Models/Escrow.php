<?php

namespace App\Models;

use App\Enums\EscrowStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $seller_id
 * @property int $buyer_id
 * @property float $amount
 * @property float $admin_commission
 * @property float $seller_amount
 * @property float $service_fee
 * @property string $status
 * @property string $payment_method
 * @property \Carbon\Carbon|null $auto_release_at
 * @property \Carbon\Carbon|null $released_at
 * @property string|null $released_by
 * @property int|null $dispute_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Order $order
 * @property-read Seller $seller
 * @property-read User $buyer
 * @property-read Dispute|null $dispute
 */
class Escrow extends Model
{
    protected $fillable = [
        'order_id',
        'seller_id',
        'buyer_id',
        'amount',
        'admin_commission',
        'seller_amount',
        'service_fee',
        'status',
        'payment_method',
        'auto_release_at',
        'released_at',
        'released_by',
        'dispute_id',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'seller_id' => 'integer',
            'buyer_id' => 'integer',
            'amount' => 'float',
            'admin_commission' => 'float',
            'seller_amount' => 'float',
            'service_fee' => 'float',
            'dispute_id' => 'integer',
            'auto_release_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function scopeHeld(Builder $query): Builder
    {
        return $query->where('status', EscrowStatus::HELD);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', EscrowStatus::HELD)
            ->whereNotNull('auto_release_at')
            ->where('auto_release_at', '<=', now());
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
