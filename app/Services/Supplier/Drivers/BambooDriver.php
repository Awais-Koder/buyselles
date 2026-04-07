<?php

namespace App\Services\Supplier\Drivers;

use App\Contracts\SupplierDriverInterface;
use App\DTOs\Supplier\BalanceResult;
use App\DTOs\Supplier\HealthResult;
use App\DTOs\Supplier\StockResult;
use App\DTOs\Supplier\SupplierOrderResult;
use App\DTOs\Supplier\SupplierProductDTO;
use App\DTOs\Supplier\WebhookResult;
use App\Models\SupplierApi;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Bamboo Card Portal API driver (V2).
 *
 * Auth: HTTP Basic Auth — ClientId as username, ClientSecret as password.
 * Host: https://api.bamboocardportal.com  (same for sandbox and production;
 *       credentials differ per environment)
 *
 * Rate limits:
 *  - Catalog V2:   1 req/s
 *  - Place Order:  2 req/s
 *  - Get Order:  120 req/min
 *  - Other:        1 req/s
 *
 * Docs: https://docs.bamboocardportal.com
 */
class BambooDriver implements SupplierDriverInterface
{
    private SupplierApi $supplier;

    /** @var array<string, mixed> */
    private array $credentials = [];

    /** @var array<string, mixed> */
    private array $settings = [];

    public function configure(SupplierApi $supplier): static
    {
        $this->supplier = $supplier;
        $this->credentials = $supplier->getDecryptedCredentials();
        $this->settings = $supplier->settings ?? [];

        return $this;
    }

    // ─── SupplierDriverInterface ──────────────────────────────────────────────

    public function authenticate(): bool
    {
        try {
            $response = $this->get('/api/integration/v2.0/catalog', [
                'PageSize' => 1,
                'PageIndex' => 0,
            ]);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Fetch the Bamboo product catalog.
     *
     * Each catalog "item" is a brand containing one or more products (SKUs).
     * We flatten brand → products into individual SupplierProductDTOs
     * so the rest of the system can map them 1-to-1.
     *
     * @param  array<string, mixed>  $filters  Accepts: country_code, currency_code,
     *                                         name, page, size, modified_since,
     *                                         brand_id, product_id, target_currency
     * @return SupplierProductDTO[]
     */
    public function fetchProducts(array $filters = []): array
    {
        $params = [
            'PageIndex' => $filters['page'] ?? 0,
            'PageSize' => $filters['size'] ?? 100,
        ];

        if (! empty($filters['country_code'])) {
            $params['CountryCode'] = strtoupper($filters['country_code']);
        }

        if (! empty($filters['currency_code'])) {
            $params['CurrencyCode'] = strtoupper($filters['currency_code']);
        }

        if (! empty($filters['name'])) {
            $params['Name'] = $filters['name'];
        }

        if (! empty($filters['modified_since'])) {
            $params['ModifiedDate'] = $filters['modified_since'];
        }

        if (! empty($filters['product_id'])) {
            $params['ProductId'] = $filters['product_id'];
        }

        if (! empty($filters['brand_id'])) {
            $params['BrandId'] = $filters['brand_id'];
        }

        if (! empty($filters['target_currency'])) {
            $params['TargetCurrency'] = strtoupper($filters['target_currency']);
        }

        $response = $this->get('/api/integration/v2.0/catalog', $params);

        if ($response->failed()) {
            throw new \RuntimeException("Bamboo fetchProducts failed: HTTP {$response->status()} — {$response->body()}");
        }

        $dtos = [];

        foreach ($response->json('items', []) as $brand) {
            $brandName = (string) ($brand['name'] ?? '');
            $countryCode = (string) ($brand['countryCode'] ?? '');
            $currencyCode = (string) ($brand['currencyCode'] ?? '');
            $description = $brand['description'] ?? null;
            $logoUrl = $brand['logoUrl'] ?? null;

            foreach ($brand['products'] ?? [] as $product) {
                $productId = (string) ($product['id'] ?? '');

                if ($productId === '') {
                    continue;
                }

                $minPrice = (float) ($product['price']['min'] ?? $product['minFaceValue'] ?? 0);
                $priceCurrency = (string) ($product['price']['currencyCode'] ?? $currencyCode);

                $dtos[] = new SupplierProductDTO(
                    supplierProductId: $productId,
                    name: (string) ($product['name'] ?? $brandName),
                    description: $description,
                    category: null,
                    imageUrl: $logoUrl,
                    price: $minPrice,
                    currency: $priceCurrency,
                    stockAvailable: ($product['count'] ?? null) !== null ? (int) $product['count'] : 999,
                    region: $countryCode ?: null,
                    rawData: array_merge($brand, ['_product' => $product]),
                );
            }
        }

        return $dtos;
    }

    /**
     * Check availability of a single Bamboo product SKU.
     *
     * Bamboo stock is soft — cards are replenished continuously.
     * We query the catalog with the specific ProductId for the freshest price/count.
     */
    public function fetchStock(string $supplierProductId): StockResult
    {
        $response = $this->get('/api/integration/v2.0/catalog', [
            'ProductId' => (int) $supplierProductId,
            'PageSize' => 1,
            'PageIndex' => 0,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("Bamboo fetchStock failed: HTTP {$response->status()}");
        }

        // Walk items → products to find the matching SKU
        foreach ($response->json('items', []) as $brand) {
            foreach ($brand['products'] ?? [] as $product) {
                if ((string) ($product['id'] ?? '') !== $supplierProductId) {
                    continue;
                }

                $available = ($product['count'] ?? null) !== null ? (int) $product['count'] : 999;
                $price = (float) ($product['price']['min'] ?? $product['minFaceValue'] ?? 0);
                $currency = (string) ($product['price']['currencyCode'] ?? $brand['currencyCode'] ?? 'USD');

                return new StockResult(
                    available: $available,
                    price: $price,
                    currency: $currency,
                    rawData: array_merge($brand, ['_product' => $product]),
                );
            }
        }

        return new StockResult(available: 0, price: 0.0, currency: 'USD', rawData: []);
    }

    /**
     * Place an order with Bamboo for the given product SKU.
     *
     * Bamboo orders are synchronous for pre-loaded stock — codes are returned
     * immediately in the response. If status is "Pending" the webhook/poll
     * will deliver codes later.
     */
    public function placeOrder(string $supplierProductId, int $quantity): SupplierOrderResult
    {
        $faceValue = (float) ($this->settings['face_value'] ?? 0);
        $currencyCode = strtoupper($this->settings['currency_code'] ?? 'USD');

        $body = [
            'ClientReference' => 'BS-' . uniqid(),
            'Products' => [
                [
                    'ProductId' => (int) $supplierProductId,
                    'Quantity' => $quantity,
                    'UnitPrice' => $faceValue > 0 ? $faceValue : null,
                    'CurrencyCode' => $currencyCode,
                ],
            ],
        ];

        // Remove null UnitPrice to let Bamboo use the default face value
        if ($body['Products'][0]['UnitPrice'] === null) {
            unset($body['Products'][0]['UnitPrice']);
        }

        $response = $this->post('/api/integration/v2.0/orders', $body);

        if ($response->failed()) {
            throw new \RuntimeException("Bamboo placeOrder failed: HTTP {$response->status()} — {$response->body()}");
        }

        $data = $response->json();
        $orderId = (string) ($data['orderId'] ?? '');
        $status = strtolower($data['status'] ?? 'pending');

        $codes = $this->extractCodes($data);

        $normalizedStatus = match ($status) {
            'completed', 'success', 'succeeded' => 'fulfilled',
            'pending', 'processing', 'inprogress' => count($codes) > 0 ? 'partial' : 'processing',
            'failed', 'error', 'cancelled', 'canceled' => 'failed',
            default => count($codes) > 0 ? 'fulfilled' : 'processing',
        };

        return new SupplierOrderResult(
            supplierOrderId: $orderId,
            status: $normalizedStatus,
            codes: $codes,
            rawResponse: $data,
        );
    }

    /**
     * Poll the status of an existing Bamboo order.
     */
    public function getOrderStatus(string $supplierOrderId): SupplierOrderResult
    {
        $response = $this->get("/api/integration/v2.0/orders/{$supplierOrderId}");

        if ($response->failed()) {
            throw new \RuntimeException("Bamboo getOrderStatus failed: HTTP {$response->status()}");
        }

        $data = $response->json();
        $status = strtolower($data['status'] ?? 'pending');
        $codes = $this->extractCodes($data);

        $normalizedStatus = match ($status) {
            'completed', 'success', 'succeeded' => 'fulfilled',
            'pending', 'processing', 'inprogress' => count($codes) > 0 ? 'partial' : 'processing',
            'failed', 'error', 'cancelled', 'canceled' => 'failed',
            default => count($codes) > 0 ? 'fulfilled' : 'processing',
        };

        return new SupplierOrderResult(
            supplierOrderId: $supplierOrderId,
            status: $normalizedStatus,
            codes: $codes,
            rawResponse: $data,
        );
    }

    /**
     * Parse and verify a Bamboo order notification webhook.
     *
     * Bamboo POSTs to the configured notification URL when an order completes.
     * The secretKey (from GET /api/Integration/v1/notification) is echoed back
     * in the webhook payload's "secretKey" field for basic verification.
     */
    public function parseWebhook(Request $request): WebhookResult
    {
        $payload = $request->all();

        // Verify the secretKey echoed back in the payload
        $expectedSecret = $this->settings['webhook_secret'] ?? null;

        if ($expectedSecret !== null) {
            $receivedSecret = $payload['secretKey'] ?? $request->header('X-Secret-Key');

            if (! hash_equals((string) $expectedSecret, (string) $receivedSecret)) {
                Log::warning('BambooDriver: webhook secretKey mismatch', [
                    'supplier_id' => $this->supplier->id,
                ]);

                return new WebhookResult(type: 'unknown', verified: false, rawPayload: $payload);
            }
        }

        $orderId = (string) ($payload['orderId'] ?? '');
        $status = strtolower($payload['status'] ?? 'unknown');
        $codes = $this->extractCodes($payload);

        $type = match ($status) {
            'completed', 'success', 'succeeded' => 'order_fulfilled',
            'failed', 'error', 'cancelled', 'canceled' => 'order_failed',
            default => 'unknown',
        };

        return new WebhookResult(
            type: $type,
            supplierOrderId: $orderId !== '' ? $orderId : null,
            codes: $codes,
            status: $status,
            verified: true,
            rawPayload: $payload,
        );
    }

    /**
     * Health check — calls catalog with minimal response to measure latency.
     */
    public function healthCheck(): HealthResult
    {
        $start = microtime(true);

        try {
            $response = $this->get('/api/integration/v2.0/catalog', [
                'PageSize' => 1,
                'PageIndex' => 0,
            ]);

            $latency = (int) ((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                return new HealthResult(
                    status: $latency > 5000 ? 'degraded' : 'healthy',
                    latencyMs: $latency,
                    message: "HTTP {$response->status()} in {$latency}ms",
                );
            }

            return new HealthResult(
                status: 'down',
                latencyMs: $latency,
                message: "HTTP {$response->status()}: {$response->body()}",
            );
        } catch (\Throwable $e) {
            $latency = (int) ((microtime(true) - $start) * 1000);

            return new HealthResult(status: 'down', latencyMs: $latency, message: $e->getMessage());
        }
    }

    /**
     * Retrieve account balance from Bamboo.
     *
     * Bamboo exposes balance information via the Transactions endpoint.
     * If no balance endpoint is available, returns unsupported.
     */
    public function getBalance(): BalanceResult
    {
        try {
            $response = $this->get('/api/Integration/v1/accounts/balance');

            if ($response->successful()) {
                $data = $response->json();

                return new BalanceResult(
                    supported: true,
                    balance: (float) ($data['balance'] ?? $data['availableBalance'] ?? 0),
                    currency: (string) ($data['currencyCode'] ?? $data['currency'] ?? 'USD'),
                );
            }

            // Bamboo may not expose a balance endpoint for all account types
            return BalanceResult::unsupported();
        } catch (\Throwable) {
            return BalanceResult::unsupported();
        }
    }

    public function getRequiredCredentialFields(): array
    {
        return [
            'client_id' => [
                'label' => 'Client ID',
                'type' => 'text',
                'required' => true,
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'type' => 'password',
                'required' => true,
            ],
        ];
    }

    public function getConfigSchema(): array
    {
        return [
            'currency_code' => [
                'label' => 'Order Currency Code (e.g. USD)',
                'type' => 'text',
                'default' => 'USD',
            ],
            'face_value' => [
                'label' => 'Default Face Value (leave empty to use catalog default)',
                'type' => 'number',
                'default' => null,
            ],
            'webhook_secret' => [
                'label' => 'Webhook Secret Key (from Bamboo notification settings)',
                'type' => 'password',
                'default' => null,
            ],
        ];
    }

    // ─── Internal helpers ─────────────────────────────────────────────────────

    /**
     * Extract redemption codes from a Bamboo order response / webhook payload.
     *
     * Bamboo returns codes under "products[].pinCode" and/or "products[].serialNumber".
     * A non-empty pinCode is the primary code; serialNumber is a secondary identifier.
     *
     * @param  array<string, mixed>  $data
     * @return string[]
     */
    private function extractCodes(array $data): array
    {
        $codes = [];

        foreach ($data['products'] ?? [] as $product) {
            $pin = trim((string) ($product['pinCode'] ?? ''));
            $serial = trim((string) ($product['serialNumber'] ?? ''));

            if ($pin !== '') {
                // If both exist, combine as "SERIAL:PIN" so nothing is lost
                $codes[] = $serial !== '' && $serial !== $pin
                    ? "{$serial}:{$pin}"
                    : $pin;
            } elseif ($serial !== '') {
                $codes[] = $serial;
            }
        }

        return $codes;
    }

    /**
     * Make a GET request with Basic Auth.
     *
     * @param  array<string, mixed>  $query
     */
    private function get(string $path, array $query = []): Response
    {
        $request = Http::withBasicAuth(
            $this->credentials['client_id'] ?? '',
            $this->credentials['client_secret'] ?? ''
        )
            ->acceptJson()
            ->timeout(30);

        $url = $this->url($path);

        return empty($query) ? $request->get($url) : $request->get($url, $query);
    }

    /**
     * Make a POST request with Basic Auth.
     *
     * @param  array<string, mixed>  $body
     */
    private function post(string $path, array $body = []): Response
    {
        return Http::withBasicAuth(
            $this->credentials['client_id'] ?? '',
            $this->credentials['client_secret'] ?? ''
        )
            ->acceptJson()
            ->timeout(60)
            ->post($this->url($path), $body);
    }

    private function url(string $path): string
    {
        $base = rtrim($this->supplier->base_url ?: 'https://api.bamboocardportal.com', '/');

        return $base . '/' . ltrim($path, '/');
    }
}
