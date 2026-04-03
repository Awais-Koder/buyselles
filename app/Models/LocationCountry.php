<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Class LocationCountry
 *
 * @property int $id
 * @property int|null $seller_id
 * @property string $name
 * @property string|null $code
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class LocationCountry extends Model
{
    protected $table = 'location_countries';

    protected $fillable = [
        'seller_id',
        'name',
        'code',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'id' => 'integer',
        'seller_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(LocationCity::class, 'country_id');
    }

    public function activeCities(): HasMany
    {
        return $this->hasMany(LocationCity::class, 'country_id')->where('is_active', true);
    }

    public function areas(): HasManyThrough
    {
        return $this->hasManyThrough(LocationArea::class, LocationCity::class, 'country_id', 'city_id');
    }
}
