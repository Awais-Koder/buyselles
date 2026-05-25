<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $category_id
 * @property string $block_type
 * @property int $position
 * @property bool $is_active
 * @property array|null $settings
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CategoryDisplayBlock extends Model
{
    protected $fillable = [
        'category_id',
        'block_type',
        'position',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'id' => 'integer',
        'category_id' => 'integer',
        'position' => 'integer',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
