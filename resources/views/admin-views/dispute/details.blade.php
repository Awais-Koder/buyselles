@extends('layouts.admin.app')

@section('title', translate('Dispute Details'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('admin.dispute.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fi fi-rr-arrow-left"></i>
            </a>
            <h2 class="h1 text-capitalize mb-0 d-flex align-items-center gap-2">
                <i class="fi fi-sr-shield-exclamation text-primary"></i>
                {{ translate('Dispute #') }}{{ $dispute->id }}
            </h2>
            @php
                $statusColors = ['open' => 'warning', 'vendor_response' => 'info', 'under_review' => 'primary', 'resolved_refund' => 'success', 'resolved_release' => 'success', 'closed' => 'secondary', 'auto_closed' => 'secondary'];
            @endphp
            <span class="badge badge-soft-{{ $statusColors[$dispute->status] ?? 'secondary' }} text-capitalize ms-2">
                {{ translate(str_replace('_', ' ', $dispute->status)) }}
            </span>
        </div>

        <div class="row g-4">
            {{-- LEFT COLUMN: Messages + Escrow --}}
            <div class="col-12 col-lg-7">

                {{-- Message Thread --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('Messages') }}</h5>
                    </div>
                    <div class="card-body" style="max-height:450px; overflow-y:auto;" id="messageThread">
                        @forelse($dispute->messages as $msg)
                            <div class="d-flex gap-3 mb-3 {{ $msg->user_type === 'admin' ? 'flex-row-reverse' : '' }}">
                                <div class="avatar avatar-sm bg-soft-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                    <i class="fi fi-rr-user"></i>
                                </div>
                                <div class="flex-grow-1 {{ $msg->user_type === 'admin' ? 'text-end' : '' }}">
                                    <div class="fw-semibold fs-12 mb-1">
                                        {{ $msg->sender?->name ?? translate('System') }}
                                        <span class="text-muted ms-1">{{ $msg->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="p-3 rounded {{ $msg->user_type === 'admin' ? 'bg-soft-primary' : 'bg-soft-secondary' }}">
                                        {{ $msg->message }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3">{{ translate('No messages yet') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Evidence --}}
                @if($dispute->evidence->count())
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ translate('Evidence') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach($dispute->evidence as $ev)
                                    <div class="col-6 col-md-4">
                                        @if($ev->file_type === 'image')
                                            <a href="{{ asset('storage/' . $ev->file_path) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $ev->file_path) }}" class="img-thumbnail w-100" alt="">
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/' . $ev->file_path) }}" class="btn btn-outline-primary w-100" target="_blank">
                                                <i class="fi fi-rr-download"></i> {{ translate('Video') }}
                                            </a>
                                        @endif
                                        @if($ev->caption)
                                            <small class="text-muted d-block mt-1">{{ $ev->caption }}</small>
                                        @endif
                                        <small class="text-muted d-block">{{ translate('By') }}: {{ ucfirst($ev->user_type) }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Escrow Card --}}
                @if($escrow)
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ translate('Escrow') }}</h5>
                            @php
                                $escrowColors = ['held' => 'warning', 'released' => 'success', 'disputed' => 'danger', 'refunded' => 'info'];
                            @endphp
                            <span class="badge badge-soft-{{ $escrowColors[$escrow->status] ?? 'secondary' }} text-capitalize">
                                {{ translate(ucfirst($escrow->status)) }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-muted fs-12">{{ translate('Amount') }}</div>
                                    <div class="fw-bold">{{ webCurrencyConverter(amount: $escrow->amount) }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted fs-12">{{ translate('Release Due') }}</div>
                                    <div class="fw-bold">{{ $escrow->auto_release_at?->format('d M Y H:i') ?? translate('N/A') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- RIGHT COLUMN: Dispute Info + Status Log + Admin Actions --}}
            <div class="col-12 col-lg-5">

                {{-- Dispute Info --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('Dispute Info') }}</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0 fs-14">
                            <dt class="col-5 text-muted">{{ translate('Order') }}</dt>
                            <dd class="col-7">
                                <a href="{{ route('admin.orders.details', $dispute->order_id) }}" class="hover-primary">
                                    #{{ $dispute->order_id }}
                                </a>
                            </dd>
                            <dt class="col-5 text-muted">{{ translate('Buyer') }}</dt>
                            <dd class="col-7">{{ $dispute->buyer?->name ?? 'N/A' }}</dd>
                            <dt class="col-5 text-muted">{{ translate('Vendor') }}</dt>
                            <dd class="col-7">{{ $dispute->vendor?->shop?->name ?? 'N/A' }}</dd>
                            <dt class="col-5 text-muted">{{ translate('Priority') }}</dt>
                            <dd class="col-7 text-capitalize">{{ $dispute->priority ?? 'medium' }}</dd>
                            <dt class="col-5 text-muted">{{ translate('Reason') }}</dt>
                            <dd class="col-7">{{ $dispute->reason?->name ?? translate('N/A') }}</dd>
                            <dt class="col-5 text-muted">{{ translate('Description') }}</dt>
                            <dd class="col-7">{{ $dispute->description }}</dd>
                            <dt class="col-5 text-muted">{{ translate('Submitted') }}</dt>
                            <dd class="col-7">{{ $dispute->created_at->format('d M Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>

                {{-- Status Log --}}
                @if($dispute->statusLogs->count())
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ translate('Status History') }}</h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($dispute->statusLogs as $log)
                                    <li class="list-group-item d-flex justify-content-between align-items-start py-2">
                                        <div>
                                            <span class="text-capitalize fw-semibold">{{ translate(str_replace('_', ' ', $log->status)) }}</span>
                                            @if($log->note)
                                                <div class="text-muted fs-12">{{ $log->note }}</div>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $log->created_at->format('d M H:i') }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Admin Action Panel --}}
                @php $canAct = !in_array($dispute->status, ['resolved_refund', 'resolved_release', 'closed', 'auto_closed']); @endphp
                @if($canAct)
                    <div class="card border-primary">
                        <div class="card-header bg-primary bg-soft">
                            <h5 class="mb-0 text-primary">{{ translate('Admin Actions') }}</h5>
                        </div>
                        <div class="card-body">

                            @if($dispute->status === 'open' || $dispute->status === 'vendor_response')
                                <form action="{{ route('admin.dispute.under-review', $dispute->id) }}" method="POST" class="mb-3">
                                    @csrf
                                    <button type="submit" class="btn btn-info w-100">
                                        <i class="fi fi-rr-eye"></i> {{ translate('Mark Under Review') }}
                                    </button>
                                </form>
                            @endif

                            @if(in_array($dispute->status, ['open', 'vendor_response', 'under_review']))
                                <form action="{{ route('admin.dispute.resolve-refund', $dispute->id) }}" method="POST" class="mb-3">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label fw-semibold">{{ translate('Decision / Admin Note') }}</label>
                                        <textarea name="decision" class="form-control" rows="3" required minlength="10"
                                            placeholder="{{ translate('Explain your decision...') }}"></textarea>
                                    </div>
                                    <input type="hidden" name="resolution_type" value="refund">
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fi fi-rr-undo"></i> {{ translate('Resolve: Refund Buyer') }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.dispute.resolve-release', $dispute->id) }}" method="POST" class="mb-3">
                                    @csrf
                                    <div class="mb-2">
                                        <textarea name="decision" class="form-control" rows="3" required minlength="10"
                                            placeholder="{{ translate('Explain your decision...') }}"></textarea>
                                    </div>
                                    <input type="hidden" name="resolution_type" value="release">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fi fi-sr-check"></i> {{ translate('Resolve: Release to Vendor') }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.dispute.close', $dispute->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-2">
                                        <textarea name="decision" class="form-control" rows="2"
                                            placeholder="{{ translate('Reason for closing (optional)') }}"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-outline-secondary w-100">
                                        <i class="fi fi-rr-cross"></i> {{ translate('Close Without Resolution') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center text-muted py-4">
                            <i class="fi fi-sr-check fs-36 d-block mb-2 text-success"></i>
                            {{ translate('This dispute has been resolved.') }}
                            @if($dispute->admin_decision)
                                <blockquote class="blockquote mt-3 fs-14">"{{ $dispute->admin_decision }}"</blockquote>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        // Auto-scroll message thread to bottom
        const thread = document.getElementById('messageThread');
        if (thread) { thread.scrollTop = thread.scrollHeight; }
    </script>
@endpush
