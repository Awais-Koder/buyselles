<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

/**
 * Class DigitalProductCode
 *
 * @property int $id
 * @property int $product_id
 * @property string $code AES-256-CBC encrypted at rest
 * @property string $status available | reserved | sold | failed
 * @property int|null $order_id
 * @property int|null $order_detail_id
 * @property Carbon|null $assigned_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DigitalProductCode extends Model
{
    protected $fillable = [
        'product_id',
        'code',
        'status',
        'order_id',
        'order_detail_id',
        'assigned_at',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'order_id' => 'integer',
        'order_detail_id' => 'integer',
        'assigned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Decrypt and return the plain-text code.
     * ONLY call this at the moment of delivery — never in list views.
     */
    public function decryptCode(): string
    {
        return Crypt::decryptString($this->code);
    }

    /**
     * Scope: only available (unassigned) codes.
     */
    public function scopeAvailable($query): mixed
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope: only sold (delivered) codes.
     */
    public function scopeSold($query): mixed
    {
        return $query->where('status', 'sold');
    }
}
