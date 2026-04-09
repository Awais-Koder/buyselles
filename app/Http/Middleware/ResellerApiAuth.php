<?php

namespace App\Http\Middleware;

use App\Models\PartnerApiLog;
use App\Models\ResellerApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ResellerApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');
        $apiSecret = $request->header('X-API-SECRET');

        if (! $apiKey || ! $apiSecret) {
            return response()->json([
                'error' => 'Missing API credentials. Provide X-API-KEY and X-API-SECRET headers.',
            ], 401);
        }

        $hashedKey = hash('sha256', $apiKey);

        $resellerKey = Cache::remember(
            "reseller_key:{$hashedKey}",
            now()->addMinutes(5),
            fn () => ResellerApiKey::with('user')->where('api_key', $hashedKey)->first()
        );

        if (! $resellerKey) {
            return response()->json(['error' => 'Invalid API key.'], 401);
        }

        if (! hash_equals($resellerKey->api_secret, hash('sha256', $apiSecret))) {
            return response()->json(['error' => 'Invalid API secret.'], 401);
        }

        if ($resellerKey->status !== 'active') {
            $msg = $resellerKey->status === 'pending'
                ? 'API key is awaiting admin approval.'
                : 'API key is deactivated.';

            return response()->json(['error' => $msg], 403);
        }

        if (! $resellerKey->isIpAllowed($request->ip())) {
            return response()->json(['error' => 'IP address not allowed.'], 403);
        }

        // ── Per-key rate limiting ─────────────────────────────────────────
        $rateKey = 'reseller_rate:'.$resellerKey->id;
        $maxAttempts = $resellerKey->rate_limit_per_minute ?? 60;

        if (RateLimiter::tooManyAttempts($rateKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($rateKey);
            $this->writeLog($resellerKey, $request, 429, 0, 'Rate limit exceeded.');

            return response()->json([
                'error' => 'Rate limit exceeded.',
                'retry_after_seconds' => $retryAfter,
            ], 429)->withHeaders([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($rateKey, 60);

        $resellerKey->recordUsage($request->ip());

        $request->attributes->set('reseller_key', $resellerKey);
        $request->attributes->set('reseller_user', $resellerKey->user);

        $startTime = microtime(true);
        $remaining = max(0, $maxAttempts - RateLimiter::attempts($rateKey));
        $response = $next($request);

        $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);
        $this->writeLog($resellerKey, $request, $response->getStatusCode(), $responseTimeMs);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
        ]);
    }

    /**
     * Write an access log entry — never records response body (codes must not leak here).
     */
    private function writeLog(ResellerApiKey $key, Request $request, int $status, int $responseTimeMs, ?string $error = null): void
    {
        try {
            PartnerApiLog::create([
                'reseller_api_key_id' => $key->id,
                'method' => $request->method(),
                'endpoint' => '/'.ltrim($request->path(), '/'),
                'http_status' => $status,
                'ip_address' => $request->ip(),
                'response_time_ms' => $responseTimeMs,
                'request_summary' => [
                    'query' => $request->query->all(),
                    'body_keys' => array_keys($request->except(['api_key', 'api_secret', 'code', 'codes'])),
                ],
                'error_message' => $error,
            ]);
        } catch (\Throwable $e) {
            // Never let logging failure break the API response
        }
    }
}
