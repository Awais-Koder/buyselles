<div class="row gy-3">
    <div class="col-md-6">
        <label class="form-label fw-bold">{{ translate('supplier') }}</label>
        <p>{{ $log->supplier?->name ?? translate('deleted') }}</p>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">{{ translate('action') }}</label>
        <p><span class="badge bg-info text-dark">{{ str_replace('_', ' ', $log->action) }}</span></p>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">{{ translate('endpoint') }}</label>
        <p class="text-break">{{ $log->endpoint ?? '-' }}</p>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">{{ translate('HTTP_method') }}</label>
        <p><span class="badge bg-secondary">{{ strtoupper($log->http_method ?? '-') }}</span></p>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">{{ translate('HTTP_status') }}</label>
        <p>
            @if($log->http_status_code)
                <span class="badge {{ $log->http_status_code >= 200 && $log->http_status_code < 300 ? 'bg-success' : 'bg-danger' }}">
                    {{ $log->http_status_code }}
                </span>
            @else
                <span class="text-muted">-</span>
            @endif
        </p>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">{{ translate('response_time') }}</label>
        <p>{{ $log->response_time_ms ? $log->response_time_ms . 'ms' : '-' }}</p>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">{{ translate('status') }}</label>
        <p>
            @if($log->status === 'success')
                <span class="badge bg-success">{{ translate('success') }}</span>
            @else
                <span class="badge bg-danger">{{ translate('failed') }}</span>
            @endif
        </p>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">{{ translate('date') }}</label>
        <p>{{ $log->created_at->format('Y-m-d H:i:s') }}</p>
    </div>

    @if($log->error_message)
    <div class="col-12">
        <label class="form-label fw-bold text-danger">{{ translate('error_message') }}</label>
        <div class="alert alert-danger">{{ $log->error_message }}</div>
    </div>
    @endif

    <div class="col-12">
        <label class="form-label fw-bold">{{ translate('request_payload') }}</label>
        <pre class="bg-light p-3 rounded" style="max-height: 250px; overflow-y: auto;"><code>{{ $log->request_payload ? json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-' }}</code></pre>
    </div>

    <div class="col-12">
        <label class="form-label fw-bold">{{ translate('response_payload') }}</label>
        <pre class="bg-light p-3 rounded" style="max-height: 250px; overflow-y: auto;"><code>{{ $log->response_payload ? json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '-' }}</code></pre>
    </div>
</div>
