@extends('layouts.admin.app')

@section('title', translate('Dispute Center'))

@section('content')
    <div class="content container-fluid">

        <h2 class="h1 text-capitalize d-flex align-items-center gap-2 mb-3">
            <i class="fi fi-sr-shield-exclamation text-primary"></i>
            {{ translate('Dispute Center') }}
        </h2>

        {{-- Stats Row --}}
        <div class="row g-2 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="d-flex gap-3 align-items-center justify-content-between p-20 bg-white rounded">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="fi fi-sr-triangle-warning fs-5"></i>
                        <h4 class="mb-0">{{ translate('Open') }}</h4>
                    </div>
                    <span class="h3 mb-0">{{ $stats['open'] }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="d-flex gap-3 align-items-center justify-content-between p-20 bg-white rounded">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="fi fi-sr-info fs-5"></i>
                        <h4 class="mb-0">{{ translate('Under Review') }}</h4>
                    </div>
                    <span class="h3 mb-0">{{ $stats['under_review'] }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="d-flex gap-3 align-items-center justify-content-between p-20 bg-white rounded">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="fi fi-sr-check fs-5"></i>
                        <h4 class="mb-0">{{ translate('Resolved') }}</h4>
                    </div>
                    <span class="h3 mb-0">{{ $stats['resolved'] }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="d-flex gap-3 align-items-center justify-content-between p-20 bg-white rounded">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="fi fi-rr-list fs-5"></i>
                        <h4 class="mb-0">{{ translate('Total') }}</h4>
                    </div>
                    <span class="h3 mb-0">{{ $stats['total'] }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="p-3">
                <div class="row g-3 justify-content-between align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-1">
                            <h3 class="mb-0 fs-16">{{ translate('Dispute List') }}</h3>
                            <span class="badge badge-soft-dark radius-50">{{ $disputes->total() }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="d-flex gap-3 flex-sm-nowrap align-items-center flex-wrap justify-content-md-end">
                            <form action="{{ url()->current() }}" method="GET" class="flex-grow-1 max-w-300 min-w-100-mobile">
                                <input type="hidden" name="status" value="{{ request('status') }}">
                                <div class="input-group">
                                    <input type="search" name="search" class="form-control"
                                        placeholder="{{ translate('Search by dispute # or order #') }}"
                                        value="{{ request('search') }}">
                                    <div class="input-group-append search-submit">
                                        <button type="submit"><i class="fi fi-rr-search"></i></button>
                                    </div>
                                </div>
                            </form>
                            <div class="position-relative">
                                @if(!empty(request('priority')) || !empty(request('from_date')) || !empty(request('to_date')))
                                    <div class="position-absolute inset-inline-end-0 top-0 mt-n1 me-n1 btn-circle bg-danger border border-white border-2" style="--size:14px;"></div>
                                @endif
                                <button type="button"
                                    class="btn {{ (!empty(request('priority')) || !empty(request('from_date')) || !empty(request('to_date'))) ? 'btn-primary' : 'btn-outline-primary' }}"
                                    data-bs-toggle="offcanvas" data-bs-target="#disputeFilter">
                                    <i class="fi fi-rr-bars-filter"></i>
                                    {{ translate('Filter') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Tabs --}}
                <div class="mt-3">
                    <ul class="nav nav-tabs nav-gap-x-1">
                        @php
                            $statuses = ['all' => translate('All'), 'open' => translate('Open'), 'vendor_response' => translate('Vendor Responded'), 'under_review' => translate('Under Review'), 'resolved_refund' => translate('Resolved: Refund'), 'resolved_release' => translate('Resolved: Released'), 'closed' => translate('Closed')];
                        @endphp
                        @foreach($statuses as $value => $label)
                            <li class="nav-item">
                                <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except('status','page'), ['status'=>$value])) }}"
                                   class="nav-link {{ request('status', 'all') === $value ? 'active' : '' }}">
                                    {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless">
                    <thead class="text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('Dispute #') }}</th>
                            <th>{{ translate('Order #') }}</th>
                            <th>{{ translate('Buyer') }}</th>
                            <th>{{ translate('Vendor') }}</th>
                            <th>{{ translate('Priority') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Submitted') }}</th>
                            <th class="text-center">{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($disputes as $index => $dispute)
                            @php
                                $priorityColors = ['low' => 'success', 'medium' => 'warning', 'high' => 'danger', 'critical' => 'danger'];
                                $statusColors = ['open' => 'warning', 'vendor_response' => 'info', 'under_review' => 'primary', 'resolved_refund' => 'success', 'resolved_release' => 'success', 'closed' => 'secondary', 'auto_closed' => 'secondary'];
                                $priority = $dispute->priority ?? 'medium';
                                $status = $dispute->status ?? 'open';
                            @endphp
                            <tr>
                                <td>{{ $disputes->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('admin.dispute.show', $dispute->id) }}" class="text-dark hover-primary fw-semibold">
                                        #{{ $dispute->id }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.details', $dispute->order_id) }}" class="text-dark hover-primary">
                                        #{{ $dispute->order_id }}
                                    </a>
                                </td>
                                <td>{{ $dispute->buyer?->name ?? translate('N/A') }}</td>
                                <td>{{ $dispute->vendor?->shop?->name ?? translate('N/A') }}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $priorityColors[$priority] ?? 'warning' }} text-capitalize">
                                        {{ translate(ucfirst($priority)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-soft-{{ $statusColors[$status] ?? 'secondary' }} text-capitalize">
                                        {{ translate(str_replace('_', ' ', $status)) }}
                                    </span>
                                </td>
                                <td>{{ $dispute->created_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.dispute.show', $dispute->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fi fi-rr-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fi fi-sr-inbox-in fs-48 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-0">{{ translate('No disputes found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex justify-content-end">
                {{ $disputes->links() }}
            </div>
        </div>
    </div>

    {{-- Filter Offcanvas --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="disputeFilter">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">{{ translate('Filter Disputes') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <form action="{{ url()->current() }}" method="GET">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <div class="mb-3">
                    <label class="form-label">{{ translate('Priority') }}</label>
                    <select name="priority" class="form-select">
                        <option value="">{{ translate('All Priorities') }}</option>
                        @foreach(['low','medium','high','critical'] as $p)
                            <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ translate(ucfirst($p)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ translate('From Date') }}</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ translate('To Date') }}</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <button type="submit" class="btn btn-primary w-100">{{ translate('Apply Filter') }}</button>
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary w-100 mt-2">{{ translate('Reset') }}</a>
            </form>
        </div>
    </div>
@endsection
