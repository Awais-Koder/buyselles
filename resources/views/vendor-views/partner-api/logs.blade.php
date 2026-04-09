@extends('layouts.vendor.app')

@section('title', translate('api_logs'))

@section('content')
<div class="content container-fluid">

    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('vendor.developer.index') }}" class="btn btn-link p-0 text-muted">
            <i class="fi fi-rr-arrow-left"></i>
        </a>
        <h1 class="mb-0 text-capitalize">{{ translate('api_access_logs') }}</h1>
    </div>

    @if(!$apiKey)
        <div class="alert alert-soft-info d-flex gap-2">
            <i class="fi fi-sr-info"></i>
            <span>{{ translate('no_api_key_found_request_one_first') }}</span>
        </div>
    @else
        <div class="card">
            <div class="card-body d-flex flex-column gap-20">

                <div class="d-flex justify-content-between align-items-center gap-20 flex-wrap">
                    <h5 class="mb-0">
                        {{ translate('logs_for') }}: <span class="text-primary">{{ $apiKey->name }}</span>
                        <span class="badge text-dark bg-body-secondary fw-semibold ms-1">{{ $logs->total() }}</span>
                    </h5>

                    {{-- Status filter --}}
                    <form action="{{ route('vendor.developer.logs') }}" method="GET">
                        <div class="input-group">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">{{ translate('all_statuses') }}</option>
                                <option value="200" {{ request('status') == '200' ? 'selected' : '' }}>200 OK</option>
                                <option value="401" {{ request('status') == '401' ? 'selected' : '' }}>401 Unauthorized</option>
                                <option value="403" {{ request('status') == '403' ? 'selected' : '' }}>403 Forbidden</option>
                                <option value="404" {{ request('status') == '404' ? 'selected' : '' }}>404 Not Found</option>
                                <option value="422" {{ request('status') == '422' ? 'selected' : '' }}>422 Validation</option>
                                <option value="429" {{ request('status') == '429' ? 'selected' : '' }}>429 Rate Limited</option>
                                <option value="500" {{ request('status') == '500' ? 'selected' : '' }}>500 Error</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="fi fi-rr-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle">
                        <thead class="text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('method') }}</th>
                                <th>{{ translate('endpoint') }}</th>
                                <th class="text-center">{{ translate('status') }}</th>
                                <th>{{ translate('ip_address') }}</th>
                                <th>{{ translate('response_time') }}</th>
                                <th>{{ translate('error') }}</th>
                                <th>{{ translate('date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($logs as $i => $log)
                            <tr>
                                <td>{{ $logs->firstItem() + $i }}</td>
                                <td><span class="badge bg-secondary font-monospace">{{ $log->method }}</span></td>
                                <td class="font-monospace small">{{ $log->endpoint }}</td>
                                <td class="text-center">
                                    @php $statusClass = $log->http_status < 300 ? 'success' : ($log->http_status < 500 ? 'warning' : 'danger'); @endphp
                                    <span class="badge bg-{{ $statusClass }}">{{ $log->http_status }}</span>
                                </td>
                                <td class="small text-muted">{{ $log->ip_address }}</td>
                                <td class="small text-muted">{{ $log->response_time_ms }}ms</td>
                                <td class="small">
                                    @if($log->error_message)
                                        <span class="text-danger" title="{{ $log->error_message }}">
                                            {{ \Illuminate\Support\Str::limit($log->error_message, 40) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small text-muted" title="{{ $log->created_at }}">
                                    {{ $log->created_at->format('d M Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center gap-2">
                                        <i class="fi fi-sr-inbox-in fs-1 text-muted"></i>
                                        <span class="text-muted">{{ translate('no_api_logs_found') }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-sm-end">
                    {{ $logs->withQueryString()->links() }}
                </div>

            </div>
        </div>
    @endif
</div>
@endsection
