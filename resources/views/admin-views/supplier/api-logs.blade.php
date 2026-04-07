@extends('layouts.admin.app')

@section('title', translate('supplier_api_logs'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header flex-wrap gap-3">
            <h5 class="mb-0"><i class="fi fi-rr-list"></i> {{ translate('supplier_API_logs') }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.supplier.api-logs') }}" method="GET" class="mb-4">
                <div class="row gy-2 gx-3 align-items-end">
                    <div class="col-lg-2">
                        <label class="form-label">{{ translate('supplier') }}</label>
                        <select name="supplier_id" class="form-control form-control-sm">
                            <option value="">{{ translate('all') }}</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">{{ translate('action') }}</label>
                        <select name="action" class="form-control form-control-sm">
                            <option value="">{{ translate('all') }}</option>
                            @foreach(['authenticate','fetch_products','fetch_stock','place_order','get_order_status','webhook','health_check'] as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', ucfirst($action)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">{{ translate('status') }}</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">{{ translate('all') }}</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>{{ translate('success') }}</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>{{ translate('failed') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">{{ translate('from') }}</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">{{ translate('to') }}</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fi fi-rr-search"></i> {{ translate('filter') }}
                        </button>
                        <a href="{{ route('admin.supplier.api-logs') }}" class="btn btn-secondary btn-sm">
                            {{ translate('reset') }}
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-borderless align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('supplier') }}</th>
                            <th>{{ translate('action') }}</th>
                            <th>{{ translate('endpoint') }}</th>
                            <th>{{ translate('method') }}</th>
                            <th>{{ translate('HTTP_code') }}</th>
                            <th>{{ translate('response_time') }}</th>
                            <th>{{ translate('status') }}</th>
                            <th>{{ translate('date') }}</th>
                            <th class="text-center">{{ translate('detail') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $key => $log)
                            <tr>
                                <td>{{ $logs->firstItem() + $key }}</td>
                                <td>{{ $log->supplier?->name ?? translate('deleted') }}</td>
                                <td><span class="badge bg-info text-dark">{{ str_replace('_', ' ', $log->action) }}</span></td>
                                <td class="text-truncate" style="max-width: 200px;" title="{{ $log->endpoint }}">{{ $log->endpoint }}</td>
                                <td><span class="badge bg-secondary">{{ strtoupper($log->http_method) }}</span></td>
                                <td>
                                    @if($log->http_status_code)
                                        <span class="badge {{ $log->http_status_code >= 200 && $log->http_status_code < 300 ? 'bg-success' : ($log->http_status_code >= 400 ? 'bg-danger' : 'bg-warning') }}">
                                            {{ $log->http_status_code }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->response_time_ms)
                                        {{ $log->response_time_ms }}ms
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->status === 'success')
                                        <span class="badge bg-success">{{ translate('success') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ translate('failed') }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-info btn-sm view-log-detail"
                                            data-url="{{ route('admin.supplier.api-log-detail', $log->id) }}">
                                        <i class="fi fi-rr-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fi fi-sr-inbox-in" style="font-size: 2rem;"></i>
                                    <p class="mt-2">{{ translate('no_logs_found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {!! $logs->links() !!}
            </div>
        </div>
    </div>
</div>

{{-- Log Detail Modal --}}
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ translate('API_log_detail') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="log-detail-content">
                <div class="text-center py-4">
                    <div class="spinner-border" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    'use strict';
    $(document).on('click', '.view-log-detail', function () {
        let url = $(this).data('url');
        let modal = new bootstrap.Modal(document.getElementById('logDetailModal'));
        $('#log-detail-content').html('<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>');
        modal.show();
        $.get(url, function (response) {
            $('#log-detail-content').html(response);
        }).fail(function () {
            $('#log-detail-content').html('<div class="alert alert-danger">{{ translate("failed_to_load_detail") }}</div>');
        });
    });
</script>
@endpush
