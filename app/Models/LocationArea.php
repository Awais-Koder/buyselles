<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LocationArea
 *
 * @property int $id
 * @property int $city_id
 * @property string $name
 * @property bool $is_active
 * @property bool $cod_available
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class LocationArea extends Model
{
    protected $table = 'location_areas';

    protected $fillable = [
        'city_id',
        'name',
        'is_active',
        'cod_available',
        'sort_order',
    ];

    protected $casts = [
        'id' => 'integer',
        'city_id' => 'integer',
        'is_active' => 'boolean',
        'cod_available' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(LocationCity::class, 'city_id');
    }
}
