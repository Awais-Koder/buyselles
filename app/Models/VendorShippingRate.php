<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class VendorShippingRate
 *
 * @property int $id
 * @property int $seller_id
 * @property int $area_id
 * @property float $shipping_cost
 * @property int|null $estimated_days
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class VendorShippingRate extends Model
{
    protected $table = 'vendor_shipping_rates';

    protected $fillable = [
        'seller_id',
        'area_id',
        'shipping_cost',
        'estimated_days',
    ];

    protected $casts = [
        'id' => 'integer',
        'seller_id' => 'integer',
        'area_id' => 'integer',
        'shipping_cost' => 'decimal:2',
        'estimated_days' => 'integer',
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
