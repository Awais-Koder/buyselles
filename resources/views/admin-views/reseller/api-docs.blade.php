@extends('layouts.admin.app')

@section('title', translate('reseller_api_documentation'))

@push('css_or_js')
<style>
    .api-endpoint { font-family: monospace; }
    .method-badge-get { background: #28a745; }
    .method-badge-post { background: #007bff; }
    .docs-section { border-left: 4px solid #6c757d; padding-left: 16px; margin-bottom: 32px; }
    .docs-section h5 { color: #343a40; }
    pre.code-block {
        background: #1e1e2e;
        color: #cdd6f4;
        border-radius: 8px;
        padding: 16px;
        font-size: 13px;
        overflow-x: auto;
    }
    .endpoint-card { border: 1px solid #e9ecef; border-radius: 8px; margin-bottom: 16px; }
    .endpoint-header { background: #f8f9fa; padding: 12px 16px; border-radius: 8px 8px 0 0; border-bottom: 1px solid #e9ecef; }
    .endpoint-body { padding: 16px; }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="mb-1">{{ translate('reseller_api_documentation') }}</h3>
                    <p class="text-muted mb-0">{{ translate('partner_api_docs_description') }}</p>
                </div>
                <a href="{{ route('admin.reseller-keys.list') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fi fi-rr-arrow-left me-1"></i>{{ translate('back_to_keys') }}
                </a>
            </div>

            {{-- Overview --}}
            <div class="docs-section">
                <h5 class="fw-bold mb-3">{{ translate('overview') }}</h5>
                <p>{{ translate('reseller_api_overview_text') }}</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="bg-light rounded p-3">
                            <div class="fw-semibold mb-1">{{ translate('base_url') }}</div>
                            <code class="api-endpoint">{{ url('/api/reseller') }}</code>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded p-3">
                            <div class="fw-semibold mb-1">{{ translate('authentication') }}</div>
                            <code>X-API-KEY: rslr_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</code>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Authentication --}}
            <div class="docs-section">
                <h5 class="fw-bold mb-3">{{ translate('authentication') }}</h5>
                <p>{{ translate('reseller_auth_description') }}</p>
                <p>Every request must include <strong>both</strong> headers:</p>
                <pre class="code-block">GET /api/reseller/products HTTP/1.1
Host: {{ parse_url(config('app.url'), PHP_URL_HOST) }}
X-API-KEY: rslr_your_api_key_here
X-API-SECRET: your_api_secret_here
Accept: application/json</pre>
                <p class="mt-3">Missing or invalid credentials return:</p>
                <pre class="code-block">// Missing headers (400)
{ "error": "Missing API credentials. Provide X-API-KEY and X-API-SECRET headers." }

// Invalid key/secret (401)
{ "error": "Invalid API key or secret." }

// Key disabled (403)
{ "error": "API key is disabled." }

// IP not whitelisted (403)
{ "error": "Access denied. Your IP address is not whitelisted." }</pre>
            </div>

            {{-- Endpoints --}}
            <div class="docs-section">
                <h5 class="fw-bold mb-4">{{ translate('endpoints') }}</h5>

                {{-- GET /products --}}
                <div class="endpoint-card">
                    <div class="endpoint-header d-flex align-items-center gap-2">
                        <span class="badge method-badge-get text-white">GET</span>
                        <code class="api-endpoint">/api/reseller/products</code>
                        <span class="text-muted ms-2">— {{ translate('list_available_products') }}</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="text-muted mb-3">{{ translate('products_endpoint_description') }}</p>
                        <div class="fw-semibold mb-2">{{ translate('query_parameters') }}</div>
                        <table class="table table-sm table-bordered mb-3">
                            <thead class="table-light"><tr><th>{{ translate('parameter') }}</th><th>{{ translate('type') }}</th><th>{{ translate('required') }}</th><th>{{ translate('description') }}</th></tr></thead>
                            <tbody>
                                <tr><td><code>search</code></td><td>string</td><td>No</td><td>{{ translate('filter_by_product_name') }}</td></tr>
                                <tr><td><code>category_id</code></td><td>integer</td><td>No</td><td>{{ translate('filter_by_category') }}</td></tr>
                                <tr><td><code>page</code></td><td>integer</td><td>No</td><td>{{ translate('page_number_default_1') }}</td></tr>
                                <tr><td><code>per_page</code></td><td>integer</td><td>No</td><td>{{ translate('items_per_page_default_20_max_100') }}</td></tr>
                            </tbody>
                        </table>
                        <div class="fw-semibold mb-2">{{ translate('response_example') }}</div>
                        <pre class="code-block">{
  "data": [
    {
      "id": 14,
      "name": "PUBG UC",
      "slug": "pubg-uc-MWOnrL",
      "category_id": 1,
      "unit_price": 20.00,
      "purchase_price": 0,
      "available_stock": 379,
      "thumbnail": {
        "key": "2026-03-28-69c7a2e7b170c.webp",
        "path": "{{ config('app.url') }}/storage/product/thumbnail/2026-03-28-69c7a2e7b170c.webp",
        "status": 200
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 98
  }
}</pre>
                    </div>
                </div>

                {{-- GET /products/{id} --}}
                <div class="endpoint-card">
                    <div class="endpoint-header d-flex align-items-center gap-2">
                        <span class="badge method-badge-get text-white">GET</span>
                        <code class="api-endpoint">/api/reseller/products/{id}</code>
                        <span class="text-muted ms-2">— {{ translate('get_product_details') }}</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="text-muted mb-3">{{ translate('product_detail_endpoint_description') }}</p>
                        <div class="fw-semibold mb-2">{{ translate('response_example') }}</div>
                        <pre class="code-block">{
  "data": {
    "id": 14,
    "name": "PUBG UC",
    "slug": "pubg-uc-MWOnrL",
    "category_id": 1,
    "sub_category_id": 2,
    "brand_id": null,
    "unit_price": 20.00,
    "purchase_price": 0,
    "available_stock": 379,
    "description": "&lt;p&gt;This is a list of 200 PUBG UC.&lt;/p&gt;",
    "thumbnail": {
      "key": "2026-03-28-69c7a2e7b170c.webp",
      "path": "{{ config('app.url') }}/storage/product/thumbnail/2026-03-28-69c7a2e7b170c.webp",
      "status": 200
    }
  }
}</pre>
                        <div class="fw-semibold mb-2 mt-3">{{ translate('error_example') }}</div>
                        <pre class="code-block">// 404 — Product not found or not a digital ready product
{ "error": "Product not found." }</pre>
                    </div>
                </div>

                {{-- POST /orders --}}
                <div class="endpoint-card">
                    <div class="endpoint-header d-flex align-items-center gap-2">
                        <span class="badge method-badge-post text-white">POST</span>
                        <code class="api-endpoint">/api/reseller/orders</code>
                        <span class="text-muted ms-2">— {{ translate('create_order') }}</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="text-muted mb-3">{{ translate('create_order_endpoint_description') }}</p>
                        <p>Deducts the total cost from your wallet instantly and returns the digital codes in the response. Only works for <strong>digital ready-stock products</strong>.</p>
                        <div class="fw-semibold mb-2">{{ translate('request_body') }} (JSON)</div>
                        <table class="table table-sm table-bordered mb-3">
                            <thead class="table-light"><tr><th>{{ translate('field') }}</th><th>{{ translate('type') }}</th><th>{{ translate('required') }}</th><th>{{ translate('description') }}</th></tr></thead>
                            <tbody>
                                <tr><td><code>product_id</code></td><td>integer</td><td>Yes</td><td>{{ translate('id_from_products_list') }}</td></tr>
                                <tr><td><code>quantity</code></td><td>integer</td><td>Yes</td><td>{{ translate('number_of_codes_1_to_100') }}</td></tr>
                                <tr><td><code>reference</code></td><td>string</td><td>No</td><td>{{ translate('your_internal_reference_for_tracking') }}</td></tr>
                            </tbody>
                        </table>
                        <pre class="code-block">{
  "product_id": 14,
  "quantity": 1,
  "reference": "your-internal-ref-001"
}</pre>
                        <div class="fw-semibold mb-2 mt-3">{{ translate('response_example') }} (201 Created)</div>
                        <pre class="code-block">{
  "data": {
    "order_id": 100053,
    "product_id": 14,
    "product_name": "PUBG UC",
    "quantity_requested": 1,
    "quantity_fulfilled": 1,
    "total_cost": 20,
    "status": "fulfilled",
    "reference": "your-internal-ref-001",
    "codes": [
      {
        "code": "TEWB-3440-DXGQ-6688",
        "serial": "SN-0021",
        "expiry": "2026-12-31"
      }
    ]
  }
}</pre>
                        <div class="fw-semibold mb-2 mt-3">{{ translate('error_examples') }}</div>
                        <pre class="code-block">// 402 — Not enough wallet balance
{ "error": "Insufficient wallet balance.", "balance": 0, "required": 20, "status": 402 }

// 409 — Not enough stock
{ "error": "Insufficient stock.", "available": 0, "requested": 1, "status": 409 }

// 404 — Product not available
{ "error": "Product not found or not available.", "status": 404 }

// 422 — Validation error
{ "errors": { "product_id": ["The product id field is required."] } }</pre>
                    </div>
                </div>

                {{-- GET /orders/{id} --}}
                <div class="endpoint-card">
                    <div class="endpoint-header d-flex align-items-center gap-2">
                        <span class="badge method-badge-get text-white">GET</span>
                        <code class="api-endpoint">/api/reseller/orders/{id}</code>
                        <span class="text-muted ms-2">— {{ translate('get_order_status') }}</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="text-muted mb-3">{{ translate('order_status_endpoint_description') }}</p>
                        <p>Returns order status and all assigned codes. You can only retrieve orders placed by your own API key.</p>
                        <div class="fw-semibold mb-2">{{ translate('response_example') }}</div>
                        <pre class="code-block">{
  "data": {
    "order_id": 100053,
    "status": "delivered",
    "payment_status": "paid",
    "total": 20.00,
    "created_at": "2026-04-07T16:33:03+00:00",
    "items": [
      {
        "product_id": 14,
        "quantity": 1,
        "price": 20.00
      }
    ],
    "codes": [
      {
        "code": "TEWB-3440-DXGQ-6688",
        "serial": "SN-0021",
        "product_id": 14,
        "expiry": "2026-12-31"
      }
    ]
  }
}</pre>
                        <div class="fw-semibold mb-2 mt-3">{{ translate('error_example') }}</div>
                        <pre class="code-block">// 404 — Order not found or belongs to another key
{ "error": "Order not found." }</pre>
                    </div>
                </div>

                {{-- GET /balance --}}
                <div class="endpoint-card">
                    <div class="endpoint-header d-flex align-items-center gap-2">
                        <span class="badge method-badge-get text-white">GET</span>
                        <code class="api-endpoint">/api/reseller/balance</code>
                        <span class="text-muted ms-2">— {{ translate('check_wallet_balance') }}</span>
                    </div>
                    <div class="endpoint-body">
                        <p class="text-muted mb-3">{{ translate('balance_endpoint_description') }}</p>
                        <div class="fw-semibold mb-2">{{ translate('response_example') }}</div>
                        <pre class="code-block">{
  "data": {
    "balance": 99980.00,
    "currency": "USD"
  }
}</pre>
                    </div>
                </div>
            </div>

            {{-- Error Codes --}}
            <div class="docs-section">
                <h5 class="fw-bold mb-3">{{ translate('error_codes') }}</h5>
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>{{ translate('http_status') }}</th>
                            <th>{{ translate('error') }}</th>
                            <th>{{ translate('description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><code>400</code></td><td>Bad Request</td><td>{{ translate('missing_api_credentials_headers') }}</td></tr>
                        <tr><td><code>401</code></td><td>Unauthorized</td><td>{{ translate('invalid_api_key_or_secret') }}</td></tr>
                        <tr><td><code>402</code></td><td>Payment Required</td><td>{{ translate('insufficient_wallet_balance') }}</td></tr>
                        <tr><td><code>403</code></td><td>Forbidden</td><td>{{ translate('api_key_disabled_or_ip_not_whitelisted') }}</td></tr>
                        <tr><td><code>404</code></td><td>Not Found</td><td>{{ translate('resource_not_found') }}</td></tr>
                        <tr><td><code>409</code></td><td>Conflict</td><td>{{ translate('insufficient_stock_for_requested_quantity') }}</td></tr>
                        <tr><td><code>422</code></td><td>Unprocessable Content</td><td>{{ translate('validation_error_check_errors_field') }}</td></tr>
                        <tr><td><code>429</code></td><td>Too Many Requests</td><td>{{ translate('rate_limit_exceeded') }}</td></tr>
                        <tr><td><code>500</code></td><td>Server Error</td><td>{{ translate('internal_server_error') }}</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- Rate Limiting --}}
            <div class="docs-section">
                <h5 class="fw-bold mb-3">{{ translate('rate_limiting') }}</h5>
                <p>{{ translate('rate_limiting_description') }}</p>
                <pre class="code-block">X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
X-RateLimit-Reset: 1712950860</pre>
            </div>
        </div>
    </div>
</div>
@endsection
