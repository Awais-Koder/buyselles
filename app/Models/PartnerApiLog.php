<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PartnerApiLog
 *
 * @property int $id
 * @property int $reseller_api_key_id
 * @property string $method
 * @property string $endpoint
 * @property int $http_status
 * @property string $ip_address
 * @property int|null $response_time_ms
 * @property array|null $request_summary
 * @property string|null $error_message
 * @property Carbon $created_at
 * @property-read ResellerApiKey $resellerApiKey
 */
class PartnerApiLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'reseller_api_key_id',
        'method',
        'endpoint',
        'http_status',
        'ip_address',
        'response_time_ms',
        'request_summary',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'reseller_api_key_id' => 'integer',
            'http_status' => 'integer',
            'response_time_ms' => 'integer',
            'request_summary' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function resellerApiKey(): BelongsTo
    {
        return $this->belongsTo(ResellerApiKey::class);
    }
}
