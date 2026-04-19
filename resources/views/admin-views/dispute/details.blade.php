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
            <span class="badge bg-{{ $statusColors[$dispute->status] ?? 'secondary' }} text-white text-capitalize ms-2">
                {{ translate(str_replace('_', ' ', $dispute->status)) }}
            </span>
            @if ($dispute->escalated_at)
                <span class="badge bg-danger text-white ms-1">
                    <i class="fi fi-sr-triangle-warning me-1"></i>{{ translate('Escalated') }}
                </span>
            @endif
        </div>

        <div class="row g-4">
            {{-- LEFT COLUMN: Messages + Escrow --}}
            <div class="col-12 col-lg-7">

                {{-- Message Thread --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">{{ translate('Messages') }}</h5>
                        <span class="badge badge-soft-info">{{ $dispute->messages->count() }}</span>
                    </div>
                    <div class="card-body" style="max-height:420px; overflow-y:auto;" id="messageThread">
                        @forelse($dispute->messages as $msg)
                            @php $isAdmin = $msg->sender_type === 'admin'; @endphp
                            <div class="d-flex gap-3 mb-3 {{ $isAdmin ? 'flex-row-reverse' : '' }}">
                                <div class="avatar avatar-sm rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                                    {{ $isAdmin ? 'bg-primary text-white' : ($msg->sender_type === 'system' ? 'bg-soft-secondary' : 'bg-soft-warning') }}">
                                    <i class="fi fi-rr-{{ $isAdmin ? 'shield-check' : ($msg->sender_type === 'vendor' ? 'store-alt' : 'user') }}"></i>
                                </div>
                                <div class="flex-grow-1 {{ $isAdmin ? 'text-end' : '' }}">
                                    <div class="fw-semibold fs-12 mb-1">
                                        {{ $msg->sender_name }}
                                        <span class="badge {{ $isAdmin ? 'bg-primary text-white' : ($msg->sender_type === 'vendor' ? 'badge-soft-warning' : 'badge-soft-secondary') }} ms-1 text-capitalize">
                                            {{ translate($msg->sender_type) }}
                                        </span>
                                        <span class="text-muted ms-1">{{ $msg->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="p-3 rounded {{ $isAdmin ? 'bg-primary text-white' : 'bg-soft-secondary' }}">
                                        {{ $msg->message }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fi fi-rr-comment-alt fs-36 d-block mb-2"></i>
                                {{ translate('No messages yet') }}
                            </div>
                        @endforelse
                    </div>

                    {{-- Admin send message form (disabled on closed disputes) --}}
                    @php $isClosed = in_array($dispute->status, ['resolved_refund', 'resolved_release', 'closed', 'auto_closed']); @endphp
                    <div class="card-footer">
                        @if(!$isClosed)
                            <form action="{{ route('admin.dispute.message', $dispute->id) }}" method="POST"
                                class="d-flex flex-column gap-2" enctype="multipart/form-data">
                                @csrf
                                <textarea name="message" class="form-control @error('message') is-invalid @enderror"
                                    rows="2" maxlength="2000"
                                    placeholder="{{ translate('Type a message to the buyer or vendor...') }}"></textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1">
                                        <input type="file" name="files[]" multiple
                                            class="form-control form-control-sm"
                                            accept="image/jpeg,image/png,video/mp4"
                                            id="adminEvidenceFiles">
                                        <small class="text-muted fs-11">{{ translate('Attach up to 5 files (JPG/PNG max 5MB, MP4 max 50MB)') }}</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary px-4 flex-shrink-0">
                                        <i class="fi fi-rr-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                        @else
                            <p class="text-muted text-center mb-0 fs-12">
                                <i class="fi fi-rr-lock me-1"></i> {{ translate('This dispute is resolved — messaging is disabled.') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Evidence --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">{{ translate('Evidence') }}</h5>
                        <span class="badge badge-soft-{{ $dispute->evidence->count() ? 'success' : 'secondary' }}">
                            {{ $dispute->evidence->count() }} {{ translate('file(s)') }}
                        </span>
                    </div>
                    <div class="card-body">
                        @if($dispute->evidence->count())
                            <div class="row g-3">
                                @foreach($dispute->evidence as $ev)
                                    <div class="col-12 col-md-6">
                                        <div class="border rounded overflow-hidden">
                                            @if($ev->file_type === 'image')
                                                <a href="{{ asset('storage/' . $ev->file_path) }}" target="_blank" title="{{ translate('Open full size') }}">
                                                    <img src="{{ asset('storage/' . $ev->file_path) }}"
                                                        class="w-100"
                                                        style="max-height:200px; object-fit:cover; cursor:zoom-in;"
                                                        alt="{{ $ev->original_name }}">
                                                </a>
                                            @else
                                                <video controls preload="metadata"
                                                    class="w-100"
                                                    style="max-height:200px; background:#000;">
                                                    <source src="{{ asset('storage/' . $ev->file_path) }}" type="video/mp4">
                                                </video>
                                            @endif
                                            <div class="px-2 py-1 bg-light d-flex align-items-center justify-content-between gap-2">
                                                <div class="fs-12 text-truncate">
                                                    <i class="fi fi-rr-{{ $ev->file_type === 'image' ? 'picture' : 'video-camera' }} me-1"></i>
                                                    <span class="fw-semibold text-capitalize">{{ translate($ev->user_type) }}</span>
                                                    @if($ev->caption)
                                                        · <span class="text-muted">{{ $ev->caption }}</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex gap-1 flex-shrink-0">
                                                    <a href="{{ asset('storage/' . $ev->file_path) }}"
                                                        target="_blank"
                                                        class="btn btn-sm btn-outline-primary py-0"
                                                        title="{{ translate('Open') }}">
                                                        <i class="fi fi-rr-arrow-up-right-from-square"></i>
                                                    </a>
                                                    <a href="{{ asset('storage/' . $ev->file_path) }}"
                                                        download="{{ $ev->original_name }}"
                                                        class="btn btn-sm btn-outline-secondary py-0"
                                                        title="{{ translate('Download') }}">
                                                        <i class="fi fi-rr-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="px-2 py-1 fs-11 text-muted border-top">
                                                {{ $ev->original_name }} &bull; {{ number_format($ev->file_size / 1024 / 1024, 2) }} MB &bull; {{ $ev->created_at->format('d M Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fi fi-rr-picture fs-36 d-block mb-2"></i>
                                {{ translate('No evidence uploaded yet') }}
                            </div>
                        @endif
                    </div>
                </div>

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
                            <dd class="col-7">{{ $dispute->reason?->title ?? translate('N/A') }}</dd>
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

                            @if($dispute->status === 'pending_closure')
                                <div class="alert alert-warning mb-3">
                                    <i class="fi fi-sr-triangle-warning me-2"></i>
                                    {{ translate('Closure is awaiting buyer confirmation.') }}
                                </div>
                            @endif

                            @if(in_array($dispute->status, ['open', 'vendor_response', 'under_review', 'pending_closure']))
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

                                @if($dispute->status !== 'pending_closure')
                                    <form action="{{ route('admin.dispute.close', $dispute->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-2">
                                            <textarea name="note" class="form-control" rows="2"
                                                placeholder="{{ translate('Reason for closing (optional)') }}"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-outline-secondary w-100">
                                            <i class="fi fi-rr-cross"></i> {{ translate('Request Closure (Buyer Must Confirm)') }}
                                        </button>
                                    </form>
                                @endif
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
