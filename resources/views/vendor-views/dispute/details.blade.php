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
        </div>

        <div class="row g-4">
            {{-- Dispute Info --}}
            <div class="col-12 col-md-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">{{ translate('Dispute Info') }}</h5></div>
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
                            <dt class="col-5 text-muted">{{ translate('Status') }}</dt>
                            <dd class="col-7 text-capitalize">{{ str_replace('_', ' ', $dispute->status) }}</dd>
                            <dt class="col-5 text-muted">{{ translate('Submitted') }}</dt>
                            <dd class="col-7">{{ $dispute->created_at->format('d M Y H:i') }}</dd>
                            <dt class="col-5 text-muted">{{ translate('Description') }}</dt>
                            <dd class="col-7">{{ $dispute->description }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Messages + Actions --}}
            <div class="col-12 col-md-8">
                {{-- Message Thread --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">{{ translate('Messages') }}</h5></div>
                    <div class="card-body" style="max-height:400px; overflow-y:auto;" id="messageThread">
                        @forelse($dispute->messages as $msg)
                            <div class="d-flex gap-3 mb-3">
                                <div class="avatar avatar-sm bg-soft-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                    <i class="fi fi-rr-user"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold fs-12 mb-1">
                                        {{ $msg->sender?->name ?? translate('System') }}
                                        <span class="badge badge-soft-secondary ms-1 text-capitalize">{{ $msg->user_type }}</span>
                                        <span class="text-muted ms-2">{{ $msg->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="p-3 bg-soft-secondary rounded">{{ $msg->message }}</div>
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
                        <div class="card-header"><h5 class="mb-0">{{ translate('Evidence') }}</h5></div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach($dispute->evidence as $ev)
                                    <div class="col-6 col-md-3">
                                        @if($ev->file_type === 'image')
                                            <a href="{{ asset('storage/' . $ev->file_path) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $ev->file_path) }}" class="img-thumbnail w-100" alt="">
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/' . $ev->file_path) }}" class="btn btn-outline-secondary w-100 btn-sm" target="_blank">
                                                <i class="fi fi-rr-download"></i> {{ translate('Video') }}
                                            </a>
                                        @endif
                                        <small class="text-muted d-block mt-1">{{ translate('By') }}: {{ ucfirst($ev->user_type) }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @php $canRespond = in_array($dispute->status, ['open', 'vendor_response']); @endphp

                @if($canRespond)
                    {{-- Vendor Respond Form --}}
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary bg-soft">
                            <h5 class="mb-0 text-primary">{{ translate('Your Response') }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('vendor.dispute.respond', $dispute->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="message" class="form-control" rows="4" required minlength="10"
                                        placeholder="{{ translate('Provide your response to the buyer\'s dispute...') }}"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fi fi-rr-paper-plane"></i> {{ translate('Submit Response') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Upload Evidence --}}
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="mb-0">{{ translate('Upload Evidence') }}</h5></div>
                        <div class="card-body">
                            <form action="{{ route('vendor.dispute.evidence.upload', $dispute->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">{{ translate('File (Image JPG/PNG max 5MB, Video MP4 max 50MB)') }}</label>
                                    <input type="file" name="file" class="form-control" accept="image/jpeg,image/png,video/mp4" required>
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="caption" class="form-control" placeholder="{{ translate('Caption (optional)') }}" maxlength="200">
                                </div>
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fi fi-sr-inbox-in"></i> {{ translate('Upload') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Escalate --}}
                    @if($dispute->status === 'vendor_response')
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <p class="text-muted mb-3">{{ translate('If you cannot resolve this with the buyer, you can escalate to admin.') }}</p>
                                <form action="{{ route('vendor.dispute.escalate', $dispute->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fi fi-sr-triangle-warning"></i> {{ translate('Escalate to Admin') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="alert alert-info">
                        <i class="fi fi-sr-info me-2"></i>
                        {{ translate('This dispute is') }} {{ translate(str_replace('_', ' ', $dispute->status)) }}.
                        {{ translate('No further action is required from you.') }}
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
