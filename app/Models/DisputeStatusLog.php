<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $dispute_id
 * @property int|null $changed_by
 * @property string $changed_by_type
 * @property string|null $from_status
 * @property string $to_status
 * @property string|null $note
 * @property \Carbon\Carbon $created_at
 */
class DisputeStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'dispute_id',
        'changed_by',
        'changed_by_type',
        'from_status',
        'to_status',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'dispute_id' => 'integer',
            'changed_by' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }
}
