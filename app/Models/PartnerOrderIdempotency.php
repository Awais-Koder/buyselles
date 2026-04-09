<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PartnerOrderIdempotency
 *
 * @property int $id
 * @property int $reseller_api_key_id
 * @property string $idempotency_key
 * @property int|null $order_id
 * @property array|null $response_payload
 * @property Carbon $created_at
 * @property-read ResellerApiKey $resellerApiKey
 * @property-read Order|null $order
 */
class PartnerOrderIdempotency extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'partner_order_idempotency';

    protected $fillable = [
        'reseller_api_key_id',
        'idempotency_key',
        'order_id',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'reseller_api_key_id' => 'integer',
            'order_id' => 'integer',
            'response_payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function resellerApiKey(): BelongsTo
    {
        return $this->belongsTo(ResellerApiKey::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
