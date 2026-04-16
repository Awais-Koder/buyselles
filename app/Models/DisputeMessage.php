<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $dispute_id
 * @property int $sender_id
 * @property string $sender_type
 * @property string $message
 * @property \Carbon\Carbon $created_at
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
}
