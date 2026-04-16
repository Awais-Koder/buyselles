@extends('layouts.admin.app')

@section('title', translate('Escrow Management'))

@section('content')
    <div class="content container-fluid">

        <h2 class="h1 text-capitalize d-flex align-items-center gap-2 mb-3">
            <i class="fi fi-sr-lock text-primary"></i>
            {{ translate('Escrow Management') }}
        </h2>

        {{-- Stats Row --}}
        <div class="row g-2 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="d-flex gap-3 align-items-center justify-content-between p-20 bg-white rounded">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="fi fi-sr-lock fs-5"></i>
                        <h4 class="mb-0">{{ translate('Held') }}</h4>
                    </div>
                    <span class="h3 mb-0">{{ $stats['held'] }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="d-flex gap-3 align-items-center justify-content-between p-20 bg-white rounded">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="fi fi-sr-shield-exclamation fs-5"></i>
                        <h4 class="mb-0">{{ translate('Disputed') }}</h4>
                    </div>
                    <span class="h3 mb-0">{{ $stats['disputed'] }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="d-flex gap-3 align-items-center justify-content-between p-20 bg-white rounded">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="fi fi-sr-unlock fs-5"></i>
                        <h4 class="mb-0">{{ translate('Released') }}</h4>
                    </div>
                    <span class="h3 mb-0">{{ $stats['released'] }}</span>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="d-flex gap-3 align-items-center justify-content-between p-20 bg-white rounded">
                    <div class="d-flex gap-3 align-items-center">
                        <i class="fi fi-sr-coins fs-5"></i>
                        <h4 class="mb-0">{{ translate('Total Held') }}</h4>
                    </div>
                    <span class="h3 mb-0">{{ webCurrencyConverter(amount: $stats['total_held_amount']) }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="p-3">
                <div class="row g-3 justify-content-between align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-1">
                            <h3 class="mb-0 fs-16">{{ translate('Escrow Records') }}</h3>
                            <span class="badge badge-soft-dark radius-50">{{ $escrows->total() }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 d-flex justify-content-md-end">
                        <form action="{{ url()->current() }}" method="GET" class="max-w-300 w-100">
                            <input type="hidden" name="status" value="{{ request('status') }}">
                            <div class="input-group">
                                <input type="search" name="search" class="form-control"
                                    placeholder="{{ translate('Search by order #') }}"
                                    value="{{ request('search') }}">
                                <div class="input-group-append search-submit">
                                    <button type="submit"><i class="fi fi-rr-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Status Tabs --}}
                <div class="mt-3">
                    <ul class="nav nav-tabs nav-gap-x-1">
                        @foreach(['all' => translate('All'), 'held' => translate('Held'), 'disputed' => translate('Disputed'), 'released' => translate('Released'), 'refunded' => translate('Refunded')] as $value => $label)
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
                            <th>{{ translate('Escrow #') }}</th>
                            <th>{{ translate('Order #') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Dispute') }}</th>
                            <th>{{ translate('Auto Release') }}</th>
                            <th class="text-center">{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($escrows as $index => $escrow)
                            @php
                                $escrowColors = ['held' => 'warning', 'released' => 'success', 'disputed' => 'danger', 'refunded' => 'info'];
                            @endphp
                            <tr>
                                <td>{{ $escrows->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('admin.escrow.show', $escrow->id) }}" class="text-dark hover-primary fw-semibold">
                                        #{{ $escrow->id }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.details', $escrow->order_id) }}" class="hover-primary">
                                        #{{ $escrow->order_id }}
                                    </a>
                                </td>
                                <td class="fw-semibold">{{ webCurrencyConverter(amount: $escrow->amount) }}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $escrowColors[$escrow->status] ?? 'secondary' }} text-capitalize">
                                        {{ translate(ucfirst($escrow->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($escrow->dispute_id)
                                        <a href="{{ route('admin.dispute.show', $escrow->dispute_id) }}" class="badge badge-soft-danger">
                                            #{{ $escrow->dispute_id }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($escrow->auto_release_at)
                                        <span class="{{ $escrow->auto_release_at->isPast() ? 'text-danger' : 'text-muted' }}">
                                            {{ $escrow->auto_release_at->format('d M Y H:i') }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.escrow.show', $escrow->id) }}" class="btn btn-outline-primary btn-sm me-1">
                                        <i class="fi fi-rr-eye"></i>
                                    </a>
                                    @if($escrow->status === 'held' && !$escrow->dispute_id)
                                        <button type="button" class="btn btn-outline-success btn-sm btn-manual-release"
                                            data-id="{{ $escrow->id }}"
                                            data-amount="{{ number_format($escrow->amount, 2) }}"
                                            data-order="{{ $escrow->order_id }}">
                                            <i class="fi fi-rr-unlock"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fi fi-sr-inbox-in fs-48 text-muted d-block mb-2"></i>
                                    <p class="text-muted mb-0">{{ translate('No escrow records found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex justify-content-end">
                {{ $escrows->links() }}
            </div>
        </div>
    </div>

    {{-- Manual Release Forms (hidden) --}}
    @foreach($escrows as $escrow)
        @if($escrow->status === 'held' && !$escrow->dispute_id)
            <form id="release-form-{{ $escrow->id }}" action="{{ route('admin.escrow.release', $escrow->id) }}" method="POST" class="d-none">
                @csrf
            </form>
        @endif
    @endforeach

    <span id="get-confirm-and-cancel-button-text-for-delete"
        data-sure="{{ translate('Are you sure?') }}"
        data-text="{{ translate('You are about to manually release escrow funds.') }}"
        data-confirm="{{ translate('Yes, Release') }}"
        data-cancel="{{ translate('Cancel') }}">
    </span>
@endsection

@push('script')
    <script>
        document.querySelectorAll('.btn-manual-release').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const order = this.dataset.order;
                const amount = this.dataset.amount;
                const getText = document.getElementById('get-confirm-and-cancel-button-text-for-delete');

                Swal.fire({
                    title: getText?.dataset.sure || 'Are you sure?',
                    text: (getText?.dataset.text || 'Release escrow') + ' Order #' + order + ' ($' + amount + ')',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: getText?.dataset.cancel || 'Cancel',
                    confirmButtonText: getText?.dataset.confirm || 'Yes, Release',
                    reverseButtons: true,
                }).then(function (result) {
                    if (result.isConfirmed) {
                        document.getElementById('release-form-' + id).submit();
                    }
                });
            });
        });
    </script>
@endpush
