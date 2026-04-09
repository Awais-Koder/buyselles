<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ResellerApiKey
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $seller_id
 * @property string $name
 * @property string $api_key SHA-256 hashed
 * @property string $api_secret SHA-256 hashed
 * @property array|null $allowed_ips
 * @property int $rate_limit_per_minute
 * @property bool $is_active
 * @property string $status pending|active|inactive
 * @property array|null $permissions
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 * @property string|null $admin_note
 * @property string|null $request_note
 * @property Carbon|null $last_used_at
 * @property string|null $last_used_ip
 * @property int $total_requests
 * @property float $wallet_balance Prepaid spending credit for API orders
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Seller|null $seller
 */
class ResellerApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'seller_id',
        'name',
        'api_key',
        'api_secret',
        'allowed_ips',
        'rate_limit_per_minute',
        'is_active',
        'status',
        'permissions',
        'approved_by',
        'approved_at',
        'admin_note',
        'request_note',
        'last_used_at',
        'last_used_ip',
        'total_requests',
        'wallet_balance',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'seller_id' => 'integer',
            'allowed_ips' => 'array',
            'rate_limit_per_minute' => 'integer',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'approved_at' => 'datetime',
            'last_used_at' => 'datetime',
            'total_requests' => 'integer',
            'wallet_balance' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PartnerApiLog::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    /**
     * Check if this key has a specific permission.
     * Permissions are stored as a list array: ['products.list', 'orders.create', ...]
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return false;
        }

        return in_array($permission, $this->permissions, true);
    }

    /**
     * Check if an IP is allowed (empty whitelist = allow all).
     */
    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->allowed_ips)) {
            return true;
        }

        return in_array($ip, $this->allowed_ips, true);
    }

    /**
     * Record a usage hit.
     */
    public function recordUsage(string $ip): void
    {
        $this->increment('total_requests');
        $this->update([
            'last_used_at' => now(),
            'last_used_ip' => $ip,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // ─── Scopes ──────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }
}
