@extends('theme-views.layouts.app')
@section('title', translate('open_dispute') . ' | ' . $web_config['company_name'])
@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-5">
        <div class="container">
            <div class="row g-3">
                @include('theme-views.partials._profile-aside')
                <div class="col-lg-9">
                    <div class="card h-100">
                        <div class="card-body p-lg-4">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <a href="{{ route('account-order-details', ['id' => $order->id]) }}"
                                    class="text-secondary">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                                <h5 class="mb-0">{{ translate('open_a_dispute') }}</h5>
                                <span class="badge badge-secondary ms-auto">{{ translate('order') }} #{{ $order->id }}</span>
                            </div>

                            {{-- Order items --}}
                            <div class="card mb-4" style="background:#f8f9fa;">
                                <div class="card-body p-3">
                                    <h6 class="fs-13 text-muted mb-2">{{ translate('order_items') }}</h6>
                                    @foreach ($order->details as $detail)
                                        <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                                            <img src="{{ $detail->product->thumbnail_image_fullpath ?? asset('themes/default/web-views/images/product_placeholder.png') }}"
                                                alt="" class="rounded"
                                                style="width:48px;height:48px;object-fit:cover;">
                                            <div class="flex-grow-1">
                                                <p class="mb-0 fs-13">{{ $detail->product_details->name ?? translate('product') }}</p>
                                                <small class="text-muted fs-11">
                                                    @if ($detail->product_type === 'digital')
                                                        <span class="badge badge-info">{{ translate('digital') }}</span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ translate('physical') }}</span>
                                                    @endif
                                                    &times; {{ $detail->qty }}
                                                </small>
                                            </div>
                                            <span class="fs-13 text-nowrap">
                                                {{ \App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usdToCurrentCurrency($detail->price)) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <form action="{{ route('open-dispute.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order->id }}">

                                <div class="mb-3">
                                    <label class="form-label fs-14">{{ translate('reason') }} <span class="text-danger">*</span></label>
                                    <select name="dispute_reason_id" class="form-control" required>
                                        <option value="">-- {{ translate('select_reason') }} --</option>
                                        @foreach ($reasons as $reason)
                                            <option value="{{ $reason->id }}"
                                                {{ old('dispute_reason_id') == $reason->id ? 'selected' : '' }}>
                                                {{ translate($reason->title) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('dispute_reason_id')
                                        <small class="text-danger fs-12">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fs-14">
                                        {{ translate('description') }} <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="description" id="dispute-desc" rows="5" class="form-control fs-13"
                                        placeholder="{{ translate('please_describe_your_issue_in_detail') }}"
                                        required minlength="20" maxlength="2000">{{ old('description') }}</textarea>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted fs-11">{{ translate('min_20_characters') }}</small>
                                        <small class="text-muted fs-11"><span id="char-count">0</span>/2000</small>
                                    </div>
                                    @error('description')
                                        <small class="text-danger fs-12">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fs-14">{{ translate('attach_evidence') }} <span class="text-muted">({{ translate('optional') }})</span></label>
                                    <div class="border rounded p-3 bg-light text-center" style="cursor:pointer;"
                                        onclick="document.getElementById('evidence-input').click();">
                                        <i class="bi bi-cloud-upload fs-3 text-muted d-block mb-1"></i>
                                        <span class="fs-13 text-muted">{{ translate('click_to_select_files') }}</span>
                                        <div class="fs-11 text-muted mt-1">JPG, PNG {{ translate('max') }} 5MB &bull; MP4 {{ translate('max') }} 50MB</div>
                                    </div>
                                    <input type="file" name="files[]" id="evidence-input" multiple
                                        accept=".jpg,.jpeg,.png,.mp4" class="d-none">
                                    <div id="file-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                                    @error('files.*')
                                        <small class="text-danger fs-12">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="alert alert-info fs-13 mb-4">
                                    <strong>{{ translate('how_it_works') }}:</strong>
                                    {{ translate('your_dispute_will_be_sent_to_the_vendor_first_they_have_48_hours_to_respond_if_unresolved_you_may_escalate_to_admin_for_final_decision') }}
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        {{ translate('submit_dispute') }}
                                    </button>
                                    <a href="{{ route('account-order-details', ['id' => $order->id]) }}"
                                        class="btn btn-light">{{ translate('cancel') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('script')
    <script>
        /* ── char counter ── */
        (function () {
            const desc    = document.getElementById('dispute-desc');
            const counter = document.getElementById('char-count');
            if (desc && counter) {
                counter.textContent = desc.value.length;
                desc.addEventListener('input', function () { counter.textContent = this.value.length; });
            }
        }());

        /* ── file upload with X-remove and per-type size limits ── */
        (function () {
            const input   = document.getElementById('evidence-input');
            const preview = document.getElementById('file-preview');

            const MAX_IMAGE_BYTES = 5  * 1024 * 1024;
            const MAX_VIDEO_BYTES = 50 * 1024 * 1024;
            const MAX_FILES       = 5;

            let dt = new DataTransfer();

            input.addEventListener('change', function () {
                Array.from(this.files).forEach(function (file) {
                    if (dt.items.length >= MAX_FILES) {
                        alert('Maximum 5 files allowed.');
                        return;
                    }
                    if (Array.from(dt.files).some(function (f) { return f.name === file.name; })) return;

                    const isVideo = file.type.startsWith('video/');
                    const limit   = isVideo ? MAX_VIDEO_BYTES : MAX_IMAGE_BYTES;
                    const label   = isVideo ? '50MB' : '5MB';

                    if (file.size > limit) {
                        alert(file.name + ' exceeds ' + label + '.');
                        return;
                    }

                    dt.items.add(file);
                    addItem(file);
                });

                this.value  = '';
                input.files = dt.files;
            });

            function addItem(file) {
                const wrap = document.createElement('div');
                wrap.className = 'position-relative';
                wrap.style.cssText = 'width:80px;height:80px;flex-shrink:0;';
                wrap.dataset.fileName = file.name;

                const btn = document.createElement('button');
                btn.type  = 'button';
                btn.title = 'Remove';
                btn.style.cssText =
                    'position:absolute;top:-7px;right:-7px;width:20px;height:20px;' +
                    'border-radius:50%;background:#dc3545;border:none;color:#fff;' +
                    'font-size:13px;cursor:pointer;z-index:5;line-height:1;padding:0;' +
                    'display:flex;align-items:center;justify-content:center;';
                btn.innerHTML = '&times;';
                btn.addEventListener('click', function () {
                    removeFile(file.name);
                    wrap.remove();
                });
                wrap.appendChild(btn);

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.className = 'rounded';
                    img.style.cssText = 'width:80px;height:80px;object-fit:cover;border:1px solid #dee2e6;display:block;';
                    img.alt = file.name;
                    const reader = new FileReader();
                    reader.onload = function (e) { img.src = e.target.result; };
                    reader.readAsDataURL(file);
                    wrap.appendChild(img);
                } else {
                    const box = document.createElement('div');
                    box.className = 'd-flex flex-column align-items-center justify-content-center h-100 rounded bg-light border fs-11 text-muted text-center p-1';
                    box.innerHTML = '<i class="bi bi-camera-video fs-3 d-block mb-1"></i>' +
                        '<span class="text-truncate" style="max-width:70px;font-size:10px;">' +
                        escHtml(file.name.length > 12 ? file.name.substring(0, 12) + '\u2026' : file.name) + '</span>';
                    wrap.appendChild(box);
                }

                preview.appendChild(wrap);
            }

            function removeFile(name) {
                const fresh = new DataTransfer();
                Array.from(dt.files).forEach(function (f) { if (f.name !== name) fresh.items.add(f); });
                dt = fresh;
                input.files = dt.files;
            }

            function escHtml(s) {
                const d = document.createElement('div');
                d.appendChild(document.createTextNode(s));
                return d.innerHTML;
            }
        }());
    </script>
@endpush
