@extends('layouts.front-end.app')

@section('title', translate('Dispute') . ' #' . $dispute->id)

@section('content')
    <div class="container py-4 rtl text-align-direction">
        <div class="row">
            @include('web-views.partials._profile-aside')

            <section class="col-lg-9 __customer-profile">

                {{-- Header --}}
                <div class="card __card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                            <div>
                                <h5 class="mb-1 fs-16 font-bold">
                                    {{ translate('dispute') }} #{{ $dispute->id }}
                                </h5>
                                <p class="mb-1 fs-13 text-muted">
                                    {{ translate('order') }}:
                                    <a href="{{ route('account-order-details', ['id' => $dispute->order_id]) }}"
                                        class="text-primary fw-semibold">#{{ $dispute->order_id }}</a>
                                </p>
                                @if ($dispute->reason)
                                    <p class="mb-0 fs-13 text-muted">
                                        {{ translate('reason') }}: {{ translate($dispute->reason->title) }}
                                    </p>
                                @endif
                            </div>
                            <div class="d-flex flex-column align-items-end gap-2">
                                @php
                                    $statusColors = [
                                        'open'             => 'badge-warning',
                                        'vendor_response'  => 'badge-info',
                                        'under_review'     => 'badge-primary',
                                        'resolved_refund'  => 'badge-success',
                                        'resolved_release' => 'badge-secondary',
                                        'closed'           => 'badge-secondary',
                                        'auto_closed'      => 'badge-secondary',
                                    ];
                                    $statusLabels = [
                                        'open'             => translate('open'),
                                        'vendor_response'  => translate('vendor_responded'),
                                        'under_review'     => translate('under_admin_review'),
                                        'resolved_refund'  => translate('resolved_refund'),
                                        'resolved_release' => translate('resolved_released'),
                                        'closed'           => translate('closed'),
                                        'auto_closed'      => translate('auto_closed'),
                                    ];
                                    $badgeClass = $statusColors[$dispute->status] ?? 'badge-secondary';
                                    $statusLabel = $statusLabels[$dispute->status] ?? translate($dispute->status);
                                @endphp
                                <span class="badge __badge rounded-full {{ $badgeClass }} fs-12 px-3 py-2">
                                    {{ $statusLabel }}
                                </span>
                                <span class="fs-11 text-muted">
                                    {{ translate('opened') }}: {{ \Carbon\Carbon::parse($dispute->created_at)->format('d M Y, h:i A') }}
                                </span>

                                {{-- Escalate button --}}
                                @if ($canEscalate && ! $isClosed)
                                    <form action="{{ route('account-dispute.escalate', $dispute->id) }}" method="POST"
                                        id="escalate-form-{{ $dispute->id }}" class="d-inline">
                                        @csrf
                                        <button type="button"
                                            class="btn btn-sm btn-outline-warning font-semibold escalate-btn"
                                            data-form-id="escalate-form-{{ $dispute->id }}">
                                            <i class="fi fi-sr-triangle-warning me-1"></i>
                                            {{ translate('escalate_to_admin') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- Admin Decision if resolved --}}
                        @if ($isClosed && $dispute->admin_decision)
                            <div class="alert alert-info mt-3 mb-0 fs-13">
                                <strong>{{ translate('admin_decision') }}:</strong> {{ $dispute->admin_decision }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Status Timeline (compact) --}}
                @if ($dispute->statusLogs && $dispute->statusLogs->count() > 0)
                    <div class="card __card mb-3">
                        <div class="card-body py-3">
                            <h6 class="fs-13 font-semibold mb-2">{{ translate('status_history') }}</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($dispute->statusLogs as $log)
                                    <div class="d-flex align-items-center gap-1 fs-11 text-muted">
                                        <span class="badge __badge badge-light">{{ translate($log->status) }}</span>
                                        <span>{{ \Carbon\Carbon::parse($log->created_at)->format('d M, h:i A') }}</span>
                                        @if (! $loop->last)
                                            <i class="fi fi-rr-angle-small-right"></i>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row g-3">
                    {{-- Conversation --}}
                    <div class="{{ $isClosed ? 'col-12' : 'col-lg-8' }}">
                        <div class="card __card h-100">
                            <div class="card-body d-flex flex-column gap-0">
                                <h6 class="fs-14 font-semibold mb-3">{{ translate('conversation') }}</h6>

                                {{-- Opening description --}}
                                <div class="d-flex gap-2 mb-3">
                                    <div class="flex-shrink-0">
                                        <span class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                            style="width:36px;height:36px;font-size:13px;font-weight:600;">
                                            {{ strtoupper(substr(auth('customer')->user()->f_name ?? 'B', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="bg-light rounded p-3 fs-13">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong class="fs-13">{{ translate('you') }}</strong>
                                                <span class="fs-11 text-muted">
                                                    {{ \Carbon\Carbon::parse($dispute->created_at)->format('d M, h:i A') }}
                                                </span>
                                            </div>
                                            <p class="mb-0" style="white-space:pre-wrap;">{{ $dispute->description }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Messages --}}
                                @foreach ($dispute->messages as $msg)
                                    @php
                                        $isBuyer  = $msg->sender_type === 'buyer';
                                        $isAdmin  = $msg->sender_type === 'admin';
                                        $isVendor = $msg->sender_type === 'vendor';
                                        $isSystem = $msg->sender_type === 'system';
                                    @endphp

                                    @if ($isSystem)
                                        <div class="text-center my-2">
                                            <small class="text-muted bg-light px-3 py-1 rounded-pill fs-11">
                                                <i class="fi fi-sr-info me-1"></i>{{ $msg->message }}
                                                &mdash; {{ \Carbon\Carbon::parse($msg->created_at)->format('d M, h:i A') }}
                                            </small>
                                        </div>
                                    @else
                                        <div class="d-flex gap-2 mb-3 {{ $isBuyer ? 'flex-row-reverse' : '' }}">
                                            <div class="flex-shrink-0">
                                                @if ($isBuyer)
                                                    <span class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                        style="width:36px;height:36px;font-size:13px;font-weight:600;">
                                                        {{ strtoupper(substr(auth('customer')->user()->f_name ?? 'B', 0, 1)) }}
                                                    </span>
                                                @elseif ($isAdmin)
                                                    <span class="avatar rounded-circle bg-danger text-white d-flex align-items-center justify-content-center"
                                                        style="width:36px;height:36px;font-size:13px;font-weight:600;">
                                                        A
                                                    </span>
                                                @else
                                                    <span class="avatar rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                                        style="width:36px;height:36px;font-size:13px;font-weight:600;">
                                                        V
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="{{ $isBuyer ? 'bg-primary text-white' : 'bg-light' }} rounded p-3 fs-13">
                                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1 gap-1">
                                                        <strong class="fs-12">
                                                            @if ($isBuyer)
                                                                {{ translate('you') }}
                                                            @elseif ($isAdmin)
                                                                {{ translate('admin') }}
                                                            @else
                                                                {{ translate('vendor') }}
                                                            @endif
                                                        </strong>
                                                        <span class="fs-11 {{ $isBuyer ? 'text-white-50' : 'text-muted' }}">
                                                            {{ \Carbon\Carbon::parse($msg->created_at)->format('d M, h:i A') }}
                                                        </span>
                                                    </div>
                                                    <p class="mb-0" style="white-space:pre-wrap;">{{ $msg->message }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                {{-- Reply form --}}
                                @if (! $isClosed)
                                    <div class="mt-3 border-top pt-3">
                                        <form action="{{ route('account-dispute.message', $dispute->id) }}" method="POST">
                                            @csrf
                                            <div class="form-group mb-2">
                                                <textarea name="message" rows="3" class="form-control fs-13"
                                                    placeholder="{{ translate('type_your_message') }}"
                                                    required minlength="3" maxlength="2000"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn--primary btn-sm font-semibold">
                                                <i class="fi fi-rr-paper-plane me-1"></i>
                                                {{ translate('send_message') }}
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="alert alert-secondary mt-3 mb-0 fs-13 text-center">
                                        <i class="fi fi-sr-lock me-1"></i>
                                        {{ translate('this_dispute_is_closed_no_new_messages_can_be_sent') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Evidence panel (only when not closed) --}}
                    @if (! $isClosed)
                        <div class="col-lg-4">
                            <div class="card __card">
                                <div class="card-body">
                                    <h6 class="fs-14 font-semibold mb-3">{{ translate('evidence') }}</h6>

                                    {{-- Existing evidence --}}
                                    @if ($dispute->evidence && $dispute->evidence->count() > 0)
                                        <div class="row g-2 mb-3">
                                            @foreach ($dispute->evidence as $ev)
                                                @php
                                                    $evUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($ev->file_path);
                                                @endphp
                                                @if ($ev->file_type === 'image')
                                                    <div class="col-6">
                                                        <a href="{{ $evUrl }}" target="_blank" title="{{ $ev->original_name }}">
                                                            <img src="{{ $evUrl }}" class="img-fluid rounded border"
                                                                style="height:90px;width:100%;object-fit:cover;"
                                                                alt="{{ $ev->original_name }}">
                                                        </a>
                                                        <small class="d-block text-muted mt-1 fs-10" style="word-break:break-all;">
                                                            {{ \Illuminate\Support\Str::limit($ev->original_name, 20) }}
                                                        </small>
                                                    </div>
                                                @else
                                                    <div class="col-12">
                                                        <a href="{{ $evUrl }}" target="_blank"
                                                            class="btn btn-sm btn-outline-secondary w-100 fs-12">
                                                            <i class="fi fi-rr-film me-1"></i>
                                                            {{ \Illuminate\Support\Str::limit($ev->original_name, 25) }}
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted fs-12 mb-3">{{ translate('no_evidence_uploaded_yet') }}</p>
                                    @endif

                                    {{-- Upload form --}}
                                    <form action="{{ route('account-dispute.evidence', $dispute->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group mb-2">
                                            <label class="fs-12 font-semibold mb-1">
                                                {{ translate('upload_evidence') }}
                                            </label>
                                            <input type="file" name="files[]" multiple
                                                class="form-control form-control-sm fs-12"
                                                accept=".jpg,.jpeg,.png,.mp4"
                                                id="evidenceFiles">
                                            <small class="text-muted fs-11 d-block mt-1">
                                                {{ translate('jpg_png_mp4_max_5_images_5MB_each_video_50MB') }}
                                            </small>
                                        </div>
                                        <button type="submit" class="btn btn--primary btn-sm font-semibold w-100">
                                            <i class="fi fi-sr-inbox-in me-1"></i>
                                            {{ translate('upload_files') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Show evidence read-only when closed --}}
                        @if ($dispute->evidence && $dispute->evidence->count() > 0)
                            <div class="col-12">
                                <div class="card __card">
                                    <div class="card-body">
                                        <h6 class="fs-14 font-semibold mb-3">{{ translate('submitted_evidence') }}</h6>
                                        <div class="row g-2">
                                            @foreach ($dispute->evidence as $ev)
                                                @php
                                                    $evUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($ev->file_path);
                                                @endphp
                                                @if ($ev->file_type === 'image')
                                                    <div class="col-6 col-md-3">
                                                        <a href="{{ $evUrl }}" target="_blank">
                                                            <img src="{{ $evUrl }}" class="img-fluid rounded border"
                                                                style="height:90px;width:100%;object-fit:cover;"
                                                                alt="{{ $ev->original_name }}">
                                                        </a>
                                                    </div>
                                                @else
                                                    <div class="col-12 col-md-6">
                                                        <a href="{{ $evUrl }}" target="_blank"
                                                            class="btn btn-sm btn-outline-secondary w-100 fs-12">
                                                            <i class="fi fi-rr-film me-1"></i>
                                                            {{ \Illuminate\Support\Str::limit($ev->original_name, 30) }}
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="mt-3">
                    <a href="{{ route('account-disputes') }}" class="text-primary fs-13">
                        <i class="fi fi-rr-arrow-left me-1"></i>{{ translate('back_to_my_disputes') }}
                    </a>
                </div>

            </section>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.querySelectorAll('.escalate-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const getText = document.getElementById('get-confirm-and-cancel-button-text-for-delete');
                Swal.fire({
                    title: '{{ addslashes(translate('escalate_to_admin_confirm_title')) }}',
                    text: '{{ addslashes(translate('escalate_to_admin_confirm_text')) }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: getText?.dataset.cancel || '{{ addslashes(translate('cancel')) }}',
                    confirmButtonText: getText?.dataset.confirm || '{{ addslashes(translate('yes_escalate')) }}',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formId = btn.getAttribute('data-form-id');
                        document.getElementById(formId).submit();
                    }
                });
            });
        });
    </script>
@endpush
