<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WalletTransaction
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $transaction_id
 * @property string|null $reference
 * @property string|null $transaction_type
 * @property float $credit
 * @property float $debit
 * @property float $admin_bonus
 * @property float $balance
 * @property array|null $order_ids
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'reference',
        'transaction_type',
        'credit',
        'debit',
        'admin_bonus',
        'balance',
        'payment_method',
        'order_ids',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'credit' => 'float',
        'debit' => 'float',
        'admin_bonus' => 'float',
        'balance' => 'float',
        'reference' => 'string',
        'order_ids' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedOrders()
    {
        if (empty($this->order_ids)) {
            return collect();
        }

        return Order::with(['orderDetails.product', 'orderDetails.seller.shop'])
            ->whereIn('id', $this->order_ids)
            ->get();
    }
}
