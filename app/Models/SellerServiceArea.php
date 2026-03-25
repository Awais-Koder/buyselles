<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SellerServiceArea
 *
 * @property int $id
 * @property int $seller_id
 * @property int $area_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SellerServiceArea extends Model
{
    protected $table = 'seller_service_areas';

    protected $fillable = [
        'seller_id',
        'area_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'seller_id' => 'integer',
        'area_id' => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(LocationArea::class, 'area_id');
    }
}
