<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $dispute_id
 * @property int $uploaded_by
 * @property string $user_type
 * @property string $file_path
 * @property string $file_type
 * @property string $original_name
 * @property int $file_size
 * @property string|null $caption
 * @property \Carbon\Carbon $created_at
 */
class DisputeEvidence extends Model
{
    public $timestamps = false;

    protected $table = 'dispute_evidence';

    protected $fillable = [
        'dispute_id',
        'uploaded_by',
        'user_type',
        'file_path',
        'file_type',
        'original_name',
        'file_size',
        'caption',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'dispute_id' => 'integer',
            'uploaded_by' => 'integer',
            'file_size' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }
}
