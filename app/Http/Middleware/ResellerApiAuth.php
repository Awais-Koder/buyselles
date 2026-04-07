<?php

namespace App\Http\Middleware;

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
            fn () => ResellerApiKey::with('user')->where('api_key_hash', $hashedKey)->first()
        );

        if (! $resellerKey) {
            return response()->json(['error' => 'Invalid API key.'], 401);
        }

        if (! hash_equals($resellerKey->api_secret_hash, hash('sha256', $apiSecret))) {
            return response()->json(['error' => 'Invalid API secret.'], 401);
        }

        if (! $resellerKey->is_active) {
            return response()->json(['error' => 'API key is deactivated.'], 403);
        }

        if (! $resellerKey->isIpAllowed($request->ip())) {
            return response()->json(['error' => 'IP address not allowed.'], 403);
        }

        // Rate limiting
        $rateKey = 'reseller_rate:'.$resellerKey->id;
        $maxAttempts = $resellerKey->rate_limit_per_minute ?? 60;

        if (RateLimiter::tooManyAttempts($rateKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($rateKey);

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

        $resellerKey->recordUsage();

        $request->attributes->set('reseller_key', $resellerKey);
        $request->attributes->set('reseller_user', $resellerKey->user);

        $remaining = max(0, $maxAttempts - RateLimiter::attempts($rateKey));
        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
        ]);
    }
}
