<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\WalletTransfer
 *
 * @property int $id
 * @property string $from_user_type
 * @property int $from_user_id
 * @property string $to_user_type
 * @property int $to_user_id
 * @property float $amount
 * @property string|null $reference
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class WalletTransfer extends Model
{
    protected $table = 'wallet_transfers';

    protected $fillable = [
        'from_user_type',
        'from_user_id',
        'to_user_type',
        'to_user_id',
        'amount',
        'reference',
    ];

    protected $casts = [
        'from_user_id' => 'integer',
        'to_user_id' => 'integer',
        'amount' => 'float',
    ];

    public function fromUser(): BelongsTo
    {
        if ($this->from_user_type === 'admin') {
            return $this->belongsTo(Admin::class, 'from_user_id');
        }

        return $this->belongsTo(Seller::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        if ($this->to_user_type === 'vendor') {
            return $this->belongsTo(Seller::class, 'to_user_id');
        }

        return $this->belongsTo(User::class, 'to_user_id');
    }
}
