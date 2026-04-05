<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AreaRequest
 *
 * @property int $id
 * @property int|null $seller_id
 * @property int $city_id
 * @property string $area_name
 * @property string $status
 * @property string|null $admin_note
 * @property int|null $approved_area_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class AreaRequest extends Model
{
    protected $table = 'area_requests';

    protected $fillable = [
        'seller_id',
        'city_id',
        'area_name',
        'status',
        'admin_note',
        'approved_area_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'seller_id' => 'integer',
        'city_id' => 'integer',
        'approved_area_id' => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(LocationCity::class, 'city_id');
    }

    public function approvedArea(): BelongsTo
    {
        return $this->belongsTo(LocationArea::class, 'approved_area_id');
    }
}
