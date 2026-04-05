<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CityRequest
 *
 * @property int $id
 * @property int $seller_id
 * @property int $country_id
 * @property string $city_name
 * @property string $status
 * @property string|null $admin_note
 * @property int|null $approved_city_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CityRequest extends Model
{
    protected $table = 'city_requests';

    protected $fillable = [
        'seller_id',
        'country_id',
        'city_name',
        'status',
        'admin_note',
        'approved_city_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'seller_id' => 'integer',
        'country_id' => 'integer',
        'approved_city_id' => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(LocationCountry::class, 'country_id');
    }

    public function approvedCity(): BelongsTo
    {
        return $this->belongsTo(LocationCity::class, 'approved_city_id');
    }
}
