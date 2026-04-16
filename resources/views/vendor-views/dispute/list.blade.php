@extends('layouts.vendor.app')

@section('title', translate('Disputes'))

@section('content')
    <div class="content container-fluid">
        <h2 class="h1 text-capitalize d-flex align-items-center gap-2 mb-3">
            <i class="fi fi-sr-shield-exclamation text-primary"></i>
            {{ translate('Disputes') }}
        </h2>

        <div class="card">
            <div class="p-3">
                <div class="row g-2 justify-content-between align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-1">
                            <h4 class="mb-0 fs-16">{{ translate('Dispute List') }}</h4>
                            <span class="badge badge-soft-dark radius-50 px-2 py-1">{{ $disputes->total() }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="d-flex gap-3 flex-sm-nowrap align-items-center flex-wrap justify-content-md-end">
                            <form action="{{ url()->current() }}" method="GET">
                                <input type="hidden" name="status" value="{{ request('status') }}">
                                <div class="input-group">
                                    <input type="search" name="search" class="form-control"
                                        placeholder="{{ translate('Search by dispute # or order #') }}"
                                        value="{{ request('search') }}">
                                    <button type="submit" class="btn btn--primary">{{ translate('Search') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Status Tabs --}}
                <div class="mt-3">
                    <ul class="nav nav-tabs nav-gap-x-1">
                        @foreach(['all' => translate('All'), 'open' => translate('Open'), 'vendor_response' => translate('Action Needed'), 'under_review' => translate('Under Review'), 'resolved_refund' => translate('Resolved: Refund'), 'resolved_release' => translate('Resolved: Released'), 'closed' => translate('Closed')] as $value => $label)
                            <li class="nav-item">
                                <a href="{{ route('vendor.dispute.index', $value) }}"
                                   class="nav-link {{ request()->segment(3, 'all') === $value || (request()->segment(3) === null && $value === 'all') ? 'active' : '' }}">
                                    {{ $label }}
                                    @if(isset($statusCounts[$value]) && $statusCounts[$value] > 0)
                                        <span class="badge badge-soft-danger ms-1">{{ $statusCounts[$value] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-nowrap table-align-middle card-table">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('Dispute #') }}</th>
                            <th>{{ translate('Order #') }}</th>
                            <th>{{ translate('Buyer') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Submitted') }}</th>
                            <th class="text-center">{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($disputes as $index => $dispute)
                            @php
                                $statusColors = ['open' => 'warning', 'vendor_response' => 'danger', 'under_review' => 'primary', 'resolved_refund' => 'success', 'resolved_release' => 'success', 'closed' => 'secondary', 'auto_closed' => 'secondary'];
                                $isActionNeeded = $dispute->status === 'open';
                            @endphp
                            <tr class="{{ $isActionNeeded ? 'table-warning' : '' }}">
                                <td>{{ $disputes->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('vendor.dispute.show', $dispute->id) }}" class="text-dark hover-primary fw-semibold">
                                        #{{ $dispute->id }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('vendor.orders.details', $dispute->order_id) }}" class="hover-primary">
                                        #{{ $dispute->order_id }}
                                    </a>
                                </td>
                                <td>{{ $dispute->buyer?->name ?? translate('N/A') }}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $statusColors[$dispute->status] ?? 'secondary' }} text-capitalize">
                                        {{ translate(str_replace('_', ' ', $dispute->status)) }}
                                    </span>
                                </td>
                                <td>{{ $dispute->created_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('vendor.dispute.show', $dispute->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fi fi-rr-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
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
@endsection
