<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $applicable_to
 * @property string $priority_default
 * @property bool $is_active
 * @property int $sort_order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Collection|Dispute[] $disputes
 */
class DisputeReason extends Model
{
    protected $fillable = [
        'title',
        'description',
        'applicable_to',
        'priority_default',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'reason_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeApplicableTo(Builder $query, string $type): Builder
    {
        return $query->whereIn('applicable_to', [$type, 'both']);
    }
}
