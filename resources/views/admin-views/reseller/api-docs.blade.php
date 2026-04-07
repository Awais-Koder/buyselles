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
                <pre class="code-block">GET /api/reseller/products HTTP/1.1
Host: {{ request()->getHost() }}
X-API-KEY: rslr_your_api_key_here
Accept: application/json</pre>
                <p class="mt-3">{{ translate('reseller_auth_error_description') }}</p>
                <pre class="code-block">{
  "error": "Unauthorized",
  "message": "Invalid or missing API key"
}</pre>
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
      "id": 1,
      "name": "Steam Wallet 10 USD",
      "slug": "steam-wallet-10-usd",
      "category_id": 3,
      "unit_price": 10.00,
      "purchase_price": 9.50,
      "available_stock": 25,
      "thumbnail": "https://example.com/thumb.jpg"
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
  "id": 1,
  "name": "Steam Wallet 10 USD",
  "slug": "steam-wallet-10-usd",
  "description": "...",
  "category_id": 3,
  "unit_price": 10.00,
  "purchase_price": 9.50,
  "available_stock": 25
}</pre>
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
                        <div class="fw-semibold mb-2">{{ translate('request_body') }} (JSON)</div>
                        <pre class="code-block">{
  "product_id": 1,
  "quantity": 2
}</pre>
                        <div class="fw-semibold mb-2 mt-3">{{ translate('response_example') }}</div>
                        <pre class="code-block">{
  "order_id": 1042,
  "status": "completed",
  "items": [
    { "code": "XXXX-YYYY-ZZZZ", "serial": null }
  ],
  "total_charged": 19.00
}</pre>
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
                        <div class="fw-semibold mb-2">{{ translate('response_example') }}</div>
                        <pre class="code-block">{
  "order_id": 1042,
  "status": "completed",
  "product_id": 1,
  "quantity": 2,
  "items": [
    { "code": "XXXX-YYYY-ZZZZ", "serial": null }
  ],
  "created_at": "2026-04-10T12:00:00Z"
}</pre>
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
  "balance": 150.00,
  "currency": "USD",
  "user_id": 42
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
                        <tr><td><code>401</code></td><td>Unauthorized</td><td>{{ translate('missing_or_invalid_api_key') }}</td></tr>
                        <tr><td><code>403</code></td><td>Forbidden</td><td>{{ translate('api_key_revoked_or_ip_not_allowed') }}</td></tr>
                        <tr><td><code>404</code></td><td>Not Found</td><td>{{ translate('resource_not_found') }}</td></tr>
                        <tr><td><code>422</code></td><td>Unprocessable Content</td><td>{{ translate('validation_error') }}</td></tr>
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
