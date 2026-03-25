<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class LocationCity
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class LocationCity extends Model
{
    protected $table = 'location_cities';

    protected $fillable = [
        'country_id',
        'name',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'id' => 'integer',
        'country_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(LocationCountry::class, 'country_id');
    }

    public function areas(): HasMany
    {
        return $this->hasMany(LocationArea::class, 'city_id');
    }

    public function activeAreas(): HasMany
    {
        return $this->hasMany(LocationArea::class, 'city_id')->where('is_active', true);
    }
}
