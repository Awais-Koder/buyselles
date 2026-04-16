<?php

namespace App\Models;

use App\Enums\DisputeStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $order_detail_id
 * @property int $buyer_id
 * @property int $vendor_id
 * @property string $initiated_by
 * @property int|null $reason_id
 * @property string $description
 * @property string $status
 * @property string $priority
 * @property string|null $admin_decision
 * @property string|null $admin_note
 * @property int|null $resolved_by
 * @property \Carbon\Carbon|null $resolved_at
 * @property \Carbon\Carbon|null $escalated_at
 * @property \Carbon\Carbon|null $vendor_deadline_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Order $order
 * @property-read OrderDetail|null $orderDetail
 * @property-read User $buyer
 * @property-read Seller $vendor
 * @property-read DisputeReason|null $reason
 * @property-read Admin|null $resolvedBy
 * @property-read Collection|DisputeEvidence[] $evidence
 * @property-read Collection|DisputeMessage[] $messages
 * @property-read Collection|DisputeStatusLog[] $statusLogs
 * @property-read Escrow|null $escrow
 */
class Dispute extends Model
{
    protected $fillable = [
        'order_id',
        'order_detail_id',
        'buyer_id',
        'vendor_id',
        'initiated_by',
        'reason_id',
        'description',
        'status',
        'priority',
        'admin_decision',
        'admin_note',
        'resolved_by',
        'resolved_at',
        'escalated_at',
        'vendor_deadline_at',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'order_detail_id' => 'integer',
            'buyer_id' => 'integer',
            'vendor_id' => 'integer',
            'reason_id' => 'integer',
            'resolved_by' => 'integer',
            'resolved_at' => 'datetime',
            'escalated_at' => 'datetime',
            'vendor_deadline_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderDetail(): BelongsTo
    {
        return $this->belongsTo(OrderDetail::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'vendor_id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(DisputeReason::class, 'reason_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'resolved_by');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(DisputeEvidence::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DisputeMessage::class)->orderBy('created_at');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(DisputeStatusLog::class)->orderByDesc('created_at');
    }

    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            DisputeStatus::OPEN,
            DisputeStatus::VENDOR_RESPONSE,
            DisputeStatus::UNDER_REVIEW,
        ]);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            DisputeStatus::OPEN,
            DisputeStatus::VENDOR_RESPONSE,
            DisputeStatus::UNDER_REVIEW,
        ]);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, [
            DisputeStatus::RESOLVED_REFUND,
            DisputeStatus::RESOLVED_RELEASE,
            DisputeStatus::CLOSED,
            DisputeStatus::AUTO_CLOSED,
        ]);
    }
}
