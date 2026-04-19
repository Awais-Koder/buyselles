@extends('layouts.vendor.app')

@section('title', translate('Dispute Details'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('vendor.dispute.index') }}" class="btn btn-outline-secondary btn-sm">
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
            @if ($dispute->escalated_at)
                <span class="badge bg-danger text-white ms-1">
                    <i class="fi fi-sr-triangle-warning me-1"></i>{{ translate('Escalated') }}
                </span>
            @endif
        </div>

        <div class="row g-4">
            {{-- LEFT COLUMN: Messages + Evidence --}}
            <div class="col-12 col-lg-7">

                {{-- Message Thread --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">{{ translate('Messages') }}</h5>
                        <span class="badge badge-soft-info">{{ $dispute->messages->count() }}</span>
                    </div>
                    <div class="card-body" style="max-height:420px; overflow-y:auto;" id="messageThread">
                        @forelse($dispute->messages as $msg)
                            @php $isVendor = $msg->sender_type === 'vendor'; @endphp
                            <div class="d-flex gap-3 mb-3 {{ $isVendor ? 'flex-row-reverse' : '' }}">
                                <div class="avatar avatar-sm rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                                    {{ $isVendor ? 'bg-primary text-white' : ($msg->sender_type === 'admin' ? 'bg-soft-info' : ($msg->sender_type === 'system' ? 'bg-soft-secondary' : 'bg-soft-warning')) }}">
                                    <i class="fi fi-rr-{{ $isVendor ? 'store-alt' : ($msg->sender_type === 'admin' ? 'shield-check' : 'user') }}"></i>
                                </div>
                                <div class="flex-grow-1 {{ $isVendor ? 'text-end' : '' }}">
                                    <div class="fw-semibold fs-12 mb-1">
                                        {{ $msg->sender_name ?? ($msg->sender?->name ?? translate('System')) }}
                                        <span class="badge {{ $isVendor ? 'bg-primary text-white' : ($msg->sender_type === 'admin' ? 'badge-soft-info' : 'badge-soft-secondary') }} ms-1 text-capitalize">
                                            {{ translate($msg->sender_type) }}
                                        </span>
                                        <span class="text-muted ms-1">{{ $msg->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="p-3 rounded {{ $isVendor ? 'bg-primary text-white' : 'bg-soft-secondary' }}">
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

                    {{-- Vendor send message form embedded in card-footer --}}
                    @php $isClosed = in_array($dispute->status, ['resolved_refund', 'resolved_release', 'closed', 'auto_closed']); @endphp
                    <div class="card-footer">
                        @if(!$isClosed)
                            <form action="{{ route('vendor.dispute.respond', $dispute->id) }}" method="POST"
                                class="d-flex flex-column gap-2">
                                @csrf
                                <textarea name="message" class="form-control @error('message') is-invalid @enderror"
                                    rows="2" maxlength="2000" required minlength="10"
                                    placeholder="{{ translate('Type a message to the buyer...') }}"></textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="d-flex justify-content-end">
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
                                                        alt="{{ $ev->original_name ?? '' }}">
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
                                                <a href="{{ asset('storage/' . $ev->file_path) }}"
                                                    target="_blank"
                                                    class="btn btn-sm btn-outline-primary py-0 flex-shrink-0"
                                                    title="{{ translate('Open') }}">
                                                    <i class="fi fi-rr-arrow-up-right-from-square"></i>
                                                </a>
                                            </div>
                                            <div class="px-2 py-1 fs-11 text-muted border-top">
                                                {{ translate('By') }}: {{ ucfirst($ev->user_type) }} &bull; {{ $ev->created_at->format('d M Y H:i') }}
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
            </div>

            {{-- RIGHT COLUMN: Dispute Info + Status Log + Vendor Actions --}}
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
                                <a href="{{ route('vendor.orders.details', $dispute->order_id) }}" class="hover-primary">
                                    #{{ $dispute->order_id }}
                                </a>
                            </dd>
                            <dt class="col-5 text-muted">{{ translate('Buyer') }}</dt>
                            <dd class="col-7">{{ $dispute->buyer?->name ?? 'N/A' }}</dd>
                            <dt class="col-5 text-muted">{{ translate('Reason') }}</dt>
                            <dd class="col-7">{{ $dispute->reason?->title ?? translate('N/A') }}</dd>
                            <dt class="col-5 text-muted">{{ translate('Status') }}</dt>
                            <dd class="col-7 text-capitalize">{{ str_replace('_', ' ', $dispute->status) }}</dd>
                            <dt class="col-5 text-muted">{{ translate('Submitted') }}</dt>
                            <dd class="col-7">{{ $dispute->created_at->format('d M Y H:i') }}</dd>
                            <dt class="col-5 text-muted">{{ translate('Description') }}</dt>
                            <dd class="col-7">{{ $dispute->description }}</dd>
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

                {{-- Vendor Actions --}}
                @php $canRespond = in_array($dispute->status, ['open', 'vendor_response']); @endphp
                @if($canRespond)
                    {{-- Upload Evidence --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">{{ translate('Upload Evidence') }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('vendor.dispute.evidence.upload', $dispute->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">{{ translate('Files (Image JPG/PNG max 5MB, Video MP4 max 50MB)') }}</label>
                                    <input type="file" name="files[]" class="form-control" accept="image/jpeg,image/png,video/mp4" multiple required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="caption" class="form-control" placeholder="{{ translate('Caption (optional)') }}" maxlength="200">
                                </div>
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fi fi-sr-inbox-in"></i> {{ translate('Upload') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Escalate --}}
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <p class="text-muted mb-3">{{ translate('If you cannot resolve this with the buyer, you can escalate to admin.') }}</p>
                            <form action="{{ route('vendor.dispute.escalate', $dispute->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fi fi-sr-triangle-warning"></i> {{ translate('Escalate to Admin') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center text-muted py-4">
                            @if ($dispute->escalated_at)
                                <i class="fi fi-sr-triangle-warning fs-36 d-block mb-2 text-warning"></i>
                                <strong>{{ translate('Under Admin Review') }}</strong>
                                <p class="mb-0 mt-2">{{ translate('This dispute has been escalated and is currently under admin review. Please await the admin decision.') }}</p>
                            @elseif(in_array($dispute->status, ['resolved_refund', 'resolved_release']))
                                <i class="fi fi-sr-check fs-36 d-block mb-2 text-success"></i>
                                {{ translate('This dispute has been resolved.') }}
                                @if($dispute->admin_decision)
                                    <blockquote class="blockquote mt-3 fs-14">"{{ $dispute->admin_decision }}"</blockquote>
                                @endif
                            @else
                                <i class="fi fi-sr-info fs-36 d-block mb-2 text-info"></i>
                                {{ translate('This dispute is') }} {{ translate(str_replace('_', ' ', $dispute->status)) }}.
                                {{ translate('No further action is required from you.') }}
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
        const thread = document.getElementById('messageThread');
        if (thread) { thread.scrollTop = thread.scrollHeight; }
    </script>
@endpush
