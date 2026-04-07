<?php

namespace App\Services\Supplier;

use App\Models\SupplierApiLog;

class SupplierApiLogger
{
    /**
     * Start logging a new API request. Returns the log ID for completion.
     */
    public function logRequest(
        int $supplierApiId,
        string $action,
        ?string $endpoint = null,
        string $method = 'GET',
        ?array $requestPayload = null,
        ?int $orderId = null,
    ): int {
        $log = SupplierApiLog::create([
            'supplier_api_id' => $supplierApiId,
            'action' => $action,
            'endpoint' => $endpoint,
            'method' => $method,
            'request_payload' => $requestPayload ? $this->sanitizePayload($requestPayload) : null,
            'status' => 'success',
            'order_id' => $orderId,
            'created_at' => now(),
        ]);

        return $log->id;
    }

    /**
     * Complete a log entry with the response data.
     */
    public function logResponse(
        int $logId,
        int $httpStatusCode,
        ?array $responsePayload,
        int $responseTimeMs,
        string $status = 'success',
    ): void {
        SupplierApiLog::where('id', $logId)->update([
            'http_status_code' => $httpStatusCode,
            'response_payload' => $responsePayload ? $this->truncatePayload($responsePayload) : null,
            'response_time_ms' => $responseTimeMs,
            'status' => $status,
        ]);
    }

    /**
     * Mark a log entry as failed with an error message.
     */
    public function logError(
        int $logId,
        string $errorMessage,
        string $status = 'failed',
        ?int $responseTimeMs = null,
    ): void {
        $data = [
            'error_message' => $errorMessage,
            'status' => $status,
        ];

        if ($responseTimeMs !== null) {
            $data['response_time_ms'] = $responseTimeMs;
        }

        SupplierApiLog::where('id', $logId)->update($data);
    }

    /**
     * Remove sensitive fields (passwords, secrets, tokens) from request payloads.
     */
    private function sanitizePayload(array $payload): array
    {
        $sensitiveKeys = ['password', 'secret', 'token', 'api_key', 'api_secret', 'authorization'];

        return collect($payload)->map(function ($value, $key) use ($sensitiveKeys) {
            if (is_string($key) && in_array(strtolower($key), $sensitiveKeys, true)) {
                return '***REDACTED***';
            }

            if (is_array($value)) {
                return $this->sanitizePayload($value);
            }

            return $value;
        })->all();
    }

    /**
     * Truncate response payload if it exceeds 10KB when serialized.
     */
    private function truncatePayload(array $payload): array
    {
        $json = json_encode($payload);

        if (strlen($json) <= 10240) {
            return $payload;
        }

        return [
            '_truncated' => true,
            '_original_size' => strlen($json),
            '_message' => 'Response payload exceeds 10KB limit — truncated.',
        ];
    }
}
