<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SupplierApiLog
 *
 * @property int $id
 * @property int $supplier_api_id
 * @property string $action
 * @property string|null $endpoint
 * @property string $method
 * @property array|null $request_payload
 * @property array|null $response_payload
 * @property int|null $http_status_code
 * @property int|null $response_time_ms
 * @property string $status success|failed|timeout|rate_limited
 * @property string|null $error_message
 * @property int|null $order_id
 * @property Carbon $created_at
 * @property-read SupplierApi $supplierApi
 * @property-read Order|null $order
 */
class SupplierApiLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'supplier_api_id',
        'action',
        'endpoint',
        'method',
        'request_payload',
        'response_payload',
        'http_status_code',
        'response_time_ms',
        'status',
        'error_message',
        'order_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'supplier_api_id' => 'integer',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'http_status_code' => 'integer',
            'response_time_ms' => 'integer',
            'order_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────

    public function supplierApi(): BelongsTo
    {
        return $this->belongsTo(SupplierApi::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
