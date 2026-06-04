<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExchangeRateLog
 *
 * @property int $id
 * @property string $currency_code
 * @property float $old_rate
 * @property float $new_rate
 * @property string $source
 * @property array|null $api_response
 * @property string $status
 * @property string|null $error_message
 * @property Carbon $created_at
 */
class ExchangeRateLog extends Model
{
    public $timestamps = false;

    protected $casts = [
        'id' => 'integer',
        'old_rate' => 'float',
        'new_rate' => 'float',
        'api_response' => 'array',
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'currency_code',
        'old_rate',
        'new_rate',
        'source',
        'api_response',
        'status',
        'error_message',
        'created_at',
    ];

    protected $table = 'exchange_rate_logs';
}
