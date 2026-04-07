<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ResellerApiKey
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $api_key SHA-256 hashed
 * @property string $api_secret SHA-256 hashed
 * @property array|null $allowed_ips
 * @property int $rate_limit_per_minute
 * @property bool $is_active
 * @property array|null $permissions
 * @property Carbon|null $last_used_at
 * @property string|null $last_used_ip
 * @property int $total_requests
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
class ResellerApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'api_key',
        'api_secret',
        'allowed_ips',
        'rate_limit_per_minute',
        'is_active',
        'permissions',
        'last_used_at',
        'last_used_ip',
        'total_requests',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'allowed_ips' => 'array',
            'rate_limit_per_minute' => 'integer',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'last_used_at' => 'datetime',
            'total_requests' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    /**
     * Check if this key has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return ($this->permissions[$permission] ?? false) === true;
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

    // ─── Scopes ──────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
