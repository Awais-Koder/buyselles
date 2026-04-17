<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $dispute_id
 * @property int $sender_id
 * @property string $sender_type buyer|vendor|admin|system
 * @property string $message
 * @property \Carbon\Carbon $created_at
 * @property-read string $sender_name
 */
class DisputeMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'dispute_id',
        'sender_id',
        'sender_type',
        'message',
        'created_at',
    ];

    protected $appends = ['sender_name'];

    protected function casts(): array
    {
        return [
            'dispute_id' => 'integer',
            'sender_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    /** Buyer who sent the message (loaded when sender_type = buyer). */
    public function buyerSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** Vendor/seller who sent the message (loaded when sender_type = vendor). */
    public function vendorSender(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'sender_id');
    }

    /** Admin who sent the message (loaded when sender_type = admin). */
    public function adminSender(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'sender_id');
    }

    /** Resolved display name for any sender type, safe against N+1 when relationships are eager-loaded. */
    public function getSenderNameAttribute(): string
    {
        return match ($this->sender_type) {
            'buyer' => $this->buyerSender?->name ?? translate('Buyer'),
            'vendor' => $this->vendorSender?->name ?? translate('Vendor'),
            'admin' => $this->adminSender?->name ?? translate('Admin'),
            default => translate('System'),
        };
    }
}
