<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $seller_id
 * @property array|null $module_access
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class VendorPermission extends Model
{
    protected $fillable = ['seller_id', 'module_access'];

    protected $casts = [
        'module_access' => 'array',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    /**
     * Check if the vendor has access to a given module.
     */
    public function hasAccess(string $module): bool
    {
        // No record or empty access array means full access (not yet restricted).
        if ($this->module_access === null || count($this->module_access) === 0) {
            return true;
        }

        return in_array($module, $this->module_access);
    }
}
