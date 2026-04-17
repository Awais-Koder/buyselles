@extends('layouts.front-end.app')

@section('title', translate('Open_Dispute') . ' — ' . translate('order') . ' #' . $order->id)

@section('content')
    <div class="container py-4 rtl text-align-direction">
        <div class="row">
            @include('web-views.partials._profile-aside')

            <section class="col-lg-9 __customer-profile">

                <div class="card __card">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <a href="{{ route('account-order-details', ['id' => $order->id]) }}"
                                class="text-muted fs-14">
                                <i class="fi fi-rr-arrow-left"></i>
                            </a>
                            <div>
                                <h5 class="mb-0 fs-16 font-bold">{{ translate('open_a_dispute') }}</h5>
                                <p class="mb-0 fs-12 text-muted">
                                    {{ translate('order') }} #{{ $order->id }}
                                    &mdash; {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}
                                </p>
                            </div>
                        </div>

                        {{-- Order items overview --}}
                        <div class="mb-4">
                            <h6 class="fs-13 font-semibold text-muted mb-2">{{ translate('order_items') }}</h6>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0 fs-13">
                                    <tbody>
                                        @foreach ($order->details as $detail)
                                            <tr>
                                                <td style="width:50px">
                                                    @if ($detail->product && $detail->product->thumbnail)
                                                        <img src="{{ getStorageImages(path: $detail->product->thumbnail, type: 'product') }}"
                                                            width="40" height="40"
                                                            class="rounded border object-fit-cover"
                                                            alt="{{ $detail->product->name }}">
                                                    @else
                                                        <span class="bg-light rounded d-flex align-items-center justify-content-center"
                                                            style="width:40px;height:40px;">
                                                            <i class="fi fi-rr-box text-muted"></i>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <p class="mb-0 fw-semibold">
                                                        {{ $detail->product?->name ?? translate('product_not_available') }}
                                                    </p>
                                                    <small class="text-muted">
                                                        x{{ $detail->qty }}
                                                        &bull;
                                                        @if ($detail->product_type === 'digital' || (isset($detail->product_details['product_type']) && $detail->product_details['product_type'] === 'digital'))
                                                            <span class="badge badge-info badge-sm" style="font-size:10px;">{{ translate('digital') }}</span>
                                                        @else
                                                            <span class="badge badge-light badge-sm" style="font-size:10px;">{{ translate('physical') }}</span>
                                                        @endif
                                                    </small>
                                                </td>
                                                <td class="align-middle text-end">
                                                    <span class="fs-13 fw-semibold">
                                                        {{ webCurrencyConverter(amount: $detail->price * $detail->qty) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <hr>

                        {{-- Dispute Form --}}
                        <form action="{{ route('open-dispute.store') }}" method="POST" enctype="multipart/form-data"
                            id="open-dispute-form">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">

                            <div class="row g-3">
                                {{-- Reason --}}
                                <div class="col-md-12">
                                    <label class="fs-13 font-semibold mb-1" for="reason_id">
                                        {{ translate('what_is_the_issue') }}?
                                        <span class="text-muted fs-11">({{ translate('optional') }})</span>
                                    </label>
                                    <select name="reason_id" id="reason_id" class="form-control fs-13">
                                        <option value="">— {{ translate('select_a_reason') }} —</option>
                                        @foreach ($reasons as $reason)
                                            <option value="{{ $reason->id }}">
                                                {{ translate($reason->title) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Description --}}
                                <div class="col-md-12">
                                    <label class="fs-13 font-semibold mb-1" for="description">
                                        {{ translate('describe_the_issue') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="description" id="description" rows="5"
                                        class="form-control fs-13"
                                        placeholder="{{ translate('please_describe_the_issue_in_detail_minimum_20_characters') }}"
                                        required minlength="20" maxlength="2000"
                                        oninput="document.getElementById('char-count').textContent = this.value.length"></textarea>
                                    <div class="d-flex justify-content-end mt-1">
                                        <small class="text-muted fs-11">
                                            <span id="char-count">0</span>/2000
                                        </small>
                                    </div>
                                </div>

                                {{-- Evidence upload --}}
                                <div class="col-md-12">
                                    <label class="fs-13 font-semibold mb-1" for="dispute-files">
                                        {{ translate('upload_evidence') }}
                                        <span class="text-muted fs-11">({{ translate('optional') }})</span>
                                    </label>
                                    <div class="border rounded p-3">
                                        <input type="file" name="files[]" id="dispute-files" multiple
                                            class="form-control form-control-sm fs-12"
                                            accept=".jpg,.jpeg,.png,.mp4">
                                        <small class="text-muted fs-11 d-block mt-2">
                                            <i class="fi fi-sr-info me-1"></i>
                                            {{ translate('accepted_jpg_png_mp4') }}
                                            &bull; {{ translate('max_5_files') }}
                                            &bull; {{ translate('images_max_5MB') }}
                                            &bull; {{ translate('videos_max_50MB') }}
                                        </small>

                                        {{-- Preview container --}}
                                        <div id="file-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                                    </div>
                                </div>

                                {{-- Info notice --}}
                                <div class="col-md-12">
                                    <div class="alert alert-info fs-12 mb-0 d-flex align-items-start gap-2">
                                        <i class="fi fi-sr-info flex-shrink-0 mt-1"></i>
                                        <div>
                                            <strong>{{ translate('how_disputes_work') }}:</strong>
                                            {{ translate('dispute_flow_explanation') }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit --}}
                                <div class="col-md-12 d-flex gap-2">
                                    <button type="submit" class="btn btn--primary font-semibold">
                                        <i class="fi fi-sr-triangle-warning me-1"></i>
                                        {{ translate('submit_dispute') }}
                                    </button>
                                    <a href="{{ route('account-order-details', ['id' => $order->id]) }}"
                                        class="btn btn-secondary font-semibold">
                                        {{ translate('cancel') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </section>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function () {
            const input   = document.getElementById('dispute-files');
            const preview = document.getElementById('file-preview');

            const MAX_IMAGE_BYTES = 5  * 1024 * 1024;   // 5 MB
            const MAX_VIDEO_BYTES = 50 * 1024 * 1024;   // 50 MB
            const MAX_FILES       = 5;

            let dt = new DataTransfer(); // canonical file list

            /* ── on every native file selection ── */
            input.addEventListener('change', function () {
                const selected = Array.from(this.files);

                selected.forEach(function (file) {
                    if (dt.items.length >= MAX_FILES) {
                        toastr.error('{{ translate('maximum_5_files_allowed') }}');
                        return;
                    }

                    // Duplicate guard (by name)
                    if (Array.from(dt.files).some(f => f.name === file.name)) return;

                    const isVideo = file.type.startsWith('video/');
                    const isImage = file.type.startsWith('image/');
                    const limit   = isVideo ? MAX_VIDEO_BYTES : MAX_IMAGE_BYTES;
                    const label   = isVideo ? '50MB' : '5MB';

                    if (file.size > limit) {
                        toastr.error(file.name + ' {{ translate('exceeds') }} ' + label);
                        return;
                    }

                    dt.items.add(file);
                    addPreviewItem(file);
                });

                // Reset value so the same file can be re-selected after removal
                this.value = '';
                // Re-sync DataTransfer to input AFTER clearing value
                input.files = dt.files;
            });

            /* ── render one preview thumbnail ── */
            function addPreviewItem(file) {
                const wrap = document.createElement('div');
                wrap.className  = 'position-relative';
                wrap.style.cssText = 'width:80px;height:80px;flex-shrink:0;';
                wrap.dataset.fileName = file.name;

                /* X remove button */
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.title = '{{ translate('remove') }}';
                btn.style.cssText =
                    'position:absolute;top:-7px;right:-7px;width:20px;height:20px;' +
                    'border-radius:50%;background:#dc3545;border:none;color:#fff;' +
                    'font-size:12px;cursor:pointer;z-index:5;line-height:1;padding:0;' +
                    'display:flex;align-items:center;justify-content:center;';
                btn.innerHTML = '&times;';
                btn.addEventListener('click', function () {
                    removeFile(file.name);
                    wrap.remove();
                });
                wrap.appendChild(btn);

                const isVideo = file.type.startsWith('video/');

                if (isVideo) {
                    const box = document.createElement('div');
                    box.className = 'd-flex flex-column align-items-center justify-content-center h-100 bg-light rounded border fs-11 text-muted text-center p-1';
                    box.innerHTML =
                        '<i class="fi fi-rr-film fs-18 d-block mb-1"></i>' +
                        '<span class="text-truncate d-block" style="max-width:70px;font-size:10px;">' +
                        escapeHtml(file.name.length > 12 ? file.name.substring(0, 12) + '…' : file.name) +
                        '</span>';
                    wrap.appendChild(box);
                } else {
                    const img = document.createElement('img');
                    img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;display:block;';
                    img.alt = file.name;
                    const reader = new FileReader();
                    reader.onload = function (e) { img.src = e.target.result; };
                    reader.readAsDataURL(file);
                    wrap.appendChild(img);
                }

                preview.appendChild(wrap);
            }

            /* ── remove a file from DataTransfer by name ── */
            function removeFile(name) {
                const fresh = new DataTransfer();
                Array.from(dt.files).forEach(function (f) {
                    if (f.name !== name) fresh.items.add(f);
                });
                dt = fresh;
                input.files = dt.files;
            }

            /* ── XSS-safe text helper ── */
            function escapeHtml(str) {
                const d = document.createElement('div');
                d.appendChild(document.createTextNode(str));
                return d.innerHTML;
            }
        }());
    </script>
@endpush
