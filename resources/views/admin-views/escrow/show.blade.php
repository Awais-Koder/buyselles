@extends('layouts.admin.app')

@section('title', translate('Escrow Details'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('admin.escrow.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fi fi-rr-arrow-left"></i>
            </a>
            <h2 class="h1 text-capitalize mb-0 d-flex align-items-center gap-2">
                <i class="fi fi-sr-lock text-primary"></i>
                {{ translate('Escrow #') }}{{ $escrow->id }}
            </h2>
            @php
                $escrowColors = ['held' => 'warning', 'released' => 'success', 'disputed' => 'danger', 'refunded' => 'info'];
            @endphp
            <span class="badge badge-soft-{{ $escrowColors[$escrow->status] ?? 'secondary' }} text-capitalize ms-2">
                {{ translate(ucfirst($escrow->status)) }}
            </span>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-7">
                {{-- Amount Breakdown --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">{{ translate('Amount Breakdown') }}</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted fs-12">{{ translate('Held Amount') }}</div>
                                <div class="fs-20 fw-bold text-warning">{{ webCurrencyConverter(amount: $escrow->amount) }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted fs-12">{{ translate('Status') }}</div>
                                <div class="fs-20 fw-bold text-capitalize">{{ translate(ucfirst($escrow->status)) }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted fs-12">{{ translate('Created At') }}</div>
                                <div>{{ $escrow->created_at->format('d M Y H:i') }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted fs-12">{{ translate('Auto Release At') }}</div>
                                <div class="{{ $escrow->auto_release_at?->isPast() ? 'text-danger' : '' }}">
                                    {{ $escrow->auto_release_at?->format('d M Y H:i') ?? translate('N/A') }}
                                </div>
                            </div>
                            @if($escrow->released_at)
                                <div class="col-6">
                                    <div class="text-muted fs-12">{{ translate('Released At') }}</div>
                                    <div class="text-success">{{ $escrow->released_at->format('d M Y H:i') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Linked Order --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">{{ translate('Linked Order') }}</h5></div>
                    <div class="card-body">
                        @if($escrow->order)
                            <dl class="row mb-0 fs-14">
                                <dt class="col-5 text-muted">{{ translate('Order #') }}</dt>
                                <dd class="col-7">
                                    <a href="{{ route('admin.orders.details', $escrow->order->id) }}" class="hover-primary">
                                        #{{ $escrow->order->id }}
                                    </a>
                                </dd>
                                <dt class="col-5 text-muted">{{ translate('Status') }}</dt>
                                <dd class="col-7 text-capitalize">{{ $escrow->order->order_status }}</dd>
                                <dt class="col-5 text-muted">{{ translate('Total') }}</dt>
                                <dd class="col-7">{{ webCurrencyConverter(amount: $escrow->order->order_amount) }}</dd>
                            </dl>
                        @else
                            <p class="text-muted mb-0">{{ translate('Order not found') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                {{-- Linked Dispute --}}
                @if($escrow->dispute)
                    <div class="card mb-4 border-danger">
                        <div class="card-header bg-danger bg-soft">
                            <h5 class="mb-0 text-danger">{{ translate('Active Dispute') }}</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0 fs-14">
                                <dt class="col-5 text-muted">{{ translate('Dispute #') }}</dt>
                                <dd class="col-7">
                                    <a href="{{ route('admin.dispute.show', $escrow->dispute->id) }}" class="hover-primary">
                                        #{{ $escrow->dispute->id }}
                                    </a>
                                </dd>
                                <dt class="col-5 text-muted">{{ translate('Status') }}</dt>
                                <dd class="col-7 text-capitalize">{{ str_replace('_', ' ', $escrow->dispute->status) }}</dd>
                            </dl>
                            <div class="alert alert-warning mt-3 mb-0 fs-12">
                                <i class="fi fi-sr-triangle-warning me-1"></i>
                                {{ translate('Manual release is blocked while a dispute is active.') }}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Manual Release --}}
                @if($escrow->status === 'held' && !$escrow->dispute_id)
                    <div class="card border-success">
                        <div class="card-header bg-success bg-soft">
                            <h5 class="mb-0 text-success">{{ translate('Manual Release') }}</h5>
                        </div>
                        <div class="card-body">
                            <p class="fs-14 text-muted">{{ translate('Release the escrow funds to the vendor immediately. This action cannot be undone.') }}</p>
                            <button type="button" class="btn btn-success w-100" id="btnManualRelease">
                                <i class="fi fi-rr-unlock"></i> {{ translate('Release Funds Now') }}
                            </button>
                            <form id="manualReleaseForm" action="{{ route('admin.escrow.release', $escrow->id) }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <span id="get-confirm-and-cancel-button-text-for-delete"
        data-sure="{{ translate('Are you sure?') }}"
        data-text="{{ translate('You are about to manually release escrow funds to the vendor. This cannot be undone.') }}"
        data-confirm="{{ translate('Yes, Release') }}"
        data-cancel="{{ translate('Cancel') }}">
    </span>
@endsection

@push('script')
    <script>
        const btn = document.getElementById('btnManualRelease');
        if (btn) {
            btn.addEventListener('click', function () {
                const getText = document.getElementById('get-confirm-and-cancel-button-text-for-delete');
                Swal.fire({
                    title: getText?.dataset.sure || 'Are you sure?',
                    text: getText?.dataset.text || 'This cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: getText?.dataset.cancel || 'Cancel',
                    confirmButtonText: getText?.dataset.confirm || 'Yes, Release',
                    reverseButtons: true,
                }).then(function (result) {
                    if (result.isConfirmed) {
                        document.getElementById('manualReleaseForm').submit();
                    }
                });
            });
        }
    </script>
@endpush
