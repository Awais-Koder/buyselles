@extends('theme-views.layouts.app')
@section('title', translate('Dispute') . ' #' . $dispute->id . ' | ' . $web_config['company_name'])
@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-5">
        <div class="container">
            <div class="row g-3">
                @include('theme-views.partials._profile-aside')
                <div class="col-lg-9">

                    {{-- Header card --}}
                    <div class="card mb-3">
                        <div class="card-body p-lg-4">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                <div>
                                    <h5 class="mb-1">{{ translate('dispute') }} #{{ $dispute->id }}</h5>
                                    <p class="mb-1 fs-13 text-muted">
                                        {{ translate('order') }}:
                                        <a href="{{ route('account-order-details', ['id' => $dispute->order_id]) }}"
                                            class="text-primary">#{{ $dispute->order_id }}</a>
                                    </p>
                                    @if ($dispute->reason)
                                        <p class="mb-0 fs-13 text-muted">{{ translate($dispute->reason->title) }}</p>
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
                                            'pending_closure'  => 'badge-warning',
                                        ];
                                        $statusLabels = [
                                            'open'             => translate('open'),
                                            'vendor_response'  => translate('vendor_response'),
                                            'under_review'     => translate('under_review'),
                                            'resolved_refund'  => translate('resolved_refund'),
                                            'resolved_release' => translate('resolved_release'),
                                            'closed'           => translate('closed'),
                                            'auto_closed'      => translate('auto_closed'),
                                            'pending_closure'  => translate('closure_pending_your_confirmation'),
                                        ];
                                        $badgeClass  = $statusColors[$dispute->status] ?? 'badge-secondary';
                                        $statusLabel = $statusLabels[$dispute->status] ?? translate($dispute->status);
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                    @if ($dispute->escalated_at)
                                        <span class="badge bg-danger text-white">
                                            <i class="fi fi-sr-triangle-warning me-1"></i>{{ translate('escalated_to_admin') }}
                                        </span>
                                    @endif
                                    @if ($canEscalate && ! $isClosed)
                                        <form action="{{ route('account-dispute.escalate', $dispute->id) }}" method="POST"
                                            id="escalate-form-{{ $dispute->id }}">
                                            @csrf
                                            <button type="button"
                                                class="btn btn-sm btn-outline-warning escalate-btn"
                                                data-form-id="escalate-form-{{ $dispute->id }}">
                                                {{ translate('escalate_to_admin') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            @if ($isClosed && $dispute->admin_decision)
                                <div class="alert alert-info mt-3 mb-0 fs-13">
                                    <strong>{{ translate('admin_decision') }}:</strong> {{ $dispute->admin_decision }}
                                </div>
                            @endif

                            {{-- Pending Closure — action required from buyer --}}
                            @if ($dispute->status === 'pending_closure')
                                <div class="alert alert-warning mt-3 mb-0 fs-13 d-flex align-items-start gap-2">
                                    <i class="fi fi-sr-triangle-warning mt-1 flex-shrink-0"></i>
                                    <div>
                                        <strong class="d-block mb-2">{{ translate('admin_has_requested_to_close_this_dispute') }}</strong>
                                        <p class="mb-2">{{ translate('please_confirm_closure_if_your_issue_is_resolved') }}</p>
                                        <form action="{{ route('account-dispute.confirm-closure', $dispute->id) }}" method="POST"
                                            id="confirm-closure-form-{{ $dispute->id }}" class="d-inline">
                                            @csrf
                                            <button type="button"
                                                class="btn btn-sm btn-warning font-semibold confirm-closure-btn"
                                                data-form-id="confirm-closure-form-{{ $dispute->id }}">
                                                <i class="fi fi-sr-check me-1"></i>
                                                {{ translate('confirm_i_am_satisfied_close_dispute') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Conversation --}}
                        <div class="{{ $isClosed ? 'col-12' : 'col-lg-8' }}">
                            <div class="card h-100">
                                <div class="card-body p-lg-4">
                                    <h6 class="fs-14 mb-3">{{ translate('conversation') }}</h6>

                                    {{-- Opening message --}}
                                    <div class="d-flex gap-2 mb-3 flex-row-reverse">
                                        <span class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width:34px;height:34px;font-size:12px;">
                                            {{ strtoupper(substr(auth('customer')->user()->f_name ?? 'B', 0, 1)) }}
                                        </span>
                                        <div>
                                            <div class="bg-primary text-white rounded p-3 fs-13">
                                                <div class="d-flex justify-content-between mb-1 gap-2">
                                                    <strong class="fs-12">{{ translate('you') }}</strong>
                                                    <span class="fs-11 text-white-50">{{ \Carbon\Carbon::parse($dispute->created_at)->format('d M, h:i A') }}</span>
                                                </div>
                                                <p class="mb-0" style="white-space:pre-wrap;">{{ $dispute->description }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @foreach ($dispute->messages as $msg)
                                        @php
                                            $isBuyer  = $msg->sender_type === 'buyer';
                                            $isSystem = $msg->sender_type === 'system';
                                            $isAdmin  = $msg->sender_type === 'admin';
                                        @endphp
                                        @if ($isSystem)
                                            <div class="text-center my-2">
                                                <small class="text-muted fs-11 bg-light px-3 py-1 rounded-pill">
                                                    {{ $msg->message }} &mdash; {{ \Carbon\Carbon::parse($msg->created_at)->format('d M, h:i A') }}
                                                </small>
                                            </div>
                                        @else
                                            <div class="d-flex gap-2 mb-3 {{ $isBuyer ? 'flex-row-reverse' : '' }}">
                                                <span class="avatar rounded-circle {{ $isBuyer ? 'bg-primary' : ($isAdmin ? 'bg-danger' : 'bg-success') }} text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                                    style="width:34px;height:34px;font-size:12px;">
                                                    {{ $isBuyer ? strtoupper(substr(auth('customer')->user()->f_name ?? 'B', 0, 1)) : ($isAdmin ? 'A' : 'V') }}
                                                </span>
                                                <div>
                                                    <div class="{{ $isBuyer ? 'bg-primary text-white' : 'bg-light' }} rounded p-3 fs-13">
                                                        <div class="d-flex justify-content-between mb-1 gap-2">
                                                            <strong class="fs-12">
                                                                {{ $isBuyer ? translate('you') : ($isAdmin ? translate('admin') : translate('vendor')) }}
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

                                    @if (! $isClosed)
                                        <div class="mt-3 pt-3 border-top">
                                            <form action="{{ route('account-dispute.message', $dispute->id) }}" method="POST">
                                                @csrf
                                                <textarea name="message" rows="3" class="form-control form-control-sm fs-13 mb-2"
                                                    placeholder="{{ translate('type_your_message') }}"
                                                    required minlength="3" maxlength="2000"></textarea>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    {{ translate('send_message') }}
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="alert alert-secondary mt-3 mb-0 fs-13 text-center">
                                            {{ translate('this_dispute_is_closed_no_new_messages_can_be_sent') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Evidence --}}
                        @if (! $isClosed)
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body p-lg-4">
                                        <h6 class="fs-14 mb-3">{{ translate('evidence') }}</h6>
                                        @if ($dispute->evidence && $dispute->evidence->count() > 0)
                                            <div class="row g-2 mb-3">
                                                @foreach ($dispute->evidence as $ev)
                                                    @php $evUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($ev->file_path); @endphp
                                                    @if ($ev->file_type === 'image')
                                                        <div class="col-6">
                                                            <a href="{{ $evUrl }}" target="_blank">
                                                                <img src="{{ $evUrl }}" class="img-fluid rounded"
                                                                    style="height:80px;width:100%;object-fit:cover;" alt="">
                                                            </a>
                                                        </div>
                                                    @else
                                                        <div class="col-12">
                                                            <a href="{{ $evUrl }}" target="_blank"
                                                                class="btn btn-sm btn-outline-secondary w-100 fs-12">
                                                                {{ \Illuminate\Support\Str::limit($ev->original_name, 25) }}
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted fs-12 mb-3">{{ translate('no_evidence_uploaded_yet') }}</p>
                                        @endif
                                        <form action="{{ route('account-dispute.evidence', $dispute->id) }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <input type="file" name="files[]" multiple
                                                class="form-control form-control-sm fs-12 mb-2"
                                                accept=".jpg,.jpeg,.png,.mp4">
                                            <small class="text-muted fs-11 d-block mb-2">
                                                {{ translate('jpg_png_mp4_max_5_images_5MB_each_video_50MB') }}
                                            </small>
                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                {{ translate('upload_files') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('account-disputes') }}" class="btn-link text-secondary d-flex align-items-baseline">
                            <i class="bi bi-chevron-left fs-12"></i> {{ translate('back_to_my_disputes') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('script')
    <script>
        document.querySelectorAll('.escalate-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: '{{ addslashes(translate('escalate_to_admin_confirm_title')) }}',
                    text: '{{ addslashes(translate('escalate_to_admin_confirm_text')) }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ addslashes(translate('yes_escalate')) }}',
                    cancelButtonText: '{{ addslashes(translate('cancel')) }}',
                    reverseButtons: true,
                }).then(function(result) {
                    if (result.value) {
                        document.getElementById(btn.getAttribute('data-form-id')).submit();
                    }
                });
            });
        });

        document.querySelectorAll('.confirm-closure-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: '{{ addslashes(translate('confirm_dispute_closure')) }}',
                    text: '{{ addslashes(translate('confirm_closure_info_text')) }}',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ addslashes(translate('yes_close_dispute')) }}',
                    cancelButtonText: '{{ addslashes(translate('cancel')) }}',
                    reverseButtons: true,
                }).then(function(result) {
                    if (result.value) {
                        document.getElementById(btn.getAttribute('data-form-id')).submit();
                    }
                });
            });
        });
    </script>
@endpush
